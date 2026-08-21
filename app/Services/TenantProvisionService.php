<?php

namespace App\Services;

use App\Events\SubscriptionLifecycleEvent;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionLog;
use App\Models\Tenant;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Provisions a new school tenant end-to-end.
 *
 * Contract:
 *   - `$hashedPassword` MUST already be a bcrypt hash. Callers hash at
 *     capture time; this class never sees or logs plaintext.
 *   - The whole method is atomic. Either the caller gets back a fully
 *     usable Tenant (central rows written, tenant DB created + migrated
 *     + seeded, owner user inserted) OR nothing at all — orphaned tenant
 *     rows and half-migrated tenant DBs are deleted on the way out.
 *   - Any failure re-throws. Callers must handle the exception (the
 *     webhook controller does this by rolling back its own transaction
 *     and returning a 500 to the gateway so it can retry).
 */
class TenantProvisionService
{
    public function provision(
        Plan $plan,
        string $subdomain,
        string $schoolName,
        string $schoolEmail,
        string $ownerName,
        string $ownerEmail,
        string $hashedPassword,      // MUST be a bcrypt hash. See Contract above.
        string $billingInterval,
        bool $isTrial = true,
        ?string $paymentRef = null,
    ): Tenant {
        // Guard: refuse anything that isn't a bcrypt hash. Prevents a caller
        // from accidentally passing a plaintext password through here (which
        // would happen silently before this class was tightened).
        if (!str_starts_with($hashedPassword, '$2y$') && !str_starts_with($hashedPassword, '$2a$')) {
            throw new \InvalidArgumentException(
                'TenantProvisionService::provision expects a pre-hashed password; got what looks like plaintext.'
            );
        }

        // Compute lifecycle dates upfront so all writes see the same clock.
        // 1 Term trial = 90 days. Termly cycle = 4 months (3 terms per year).
        $trialDays = $plan->trial_days ?: 90;
        $startsAt = now();
        $endsAt = $isTrial
            ? now()->addDays($trialDays)
            : ($billingInterval === 'yearly' ? now()->addYear() : now()->addMonths(4));
        $status = $isTrial ? 'trialing' : 'active';

        // Resolve the tenant domain hostname. Prefer explicit config (so we
        // work correctly from queue jobs & artisan where there's no request),
        // fall back to the current request host for legacy compatibility.
        $host = config('app.tenant_domain_suffix') ?: (request()?->getHost() ?? 'localhost');
        $domainName = $subdomain . '.' . $host;

        $tenant = null;
        $createdDomainId = null;

        try {
            // Central-DB writes: tenant + domain + subscription + invoice.
            // The whole block is wrapped in a transaction so a mid-way
            // failure rolls back all four rows together.
            $tenant = DB::transaction(function () use (
                $plan, $subdomain, $schoolName, $domainName,
                $status, $isTrial, $startsAt, $endsAt, $billingInterval,
                $paymentRef, &$createdDomainId,
            ) {
                $tenant = Tenant::create([
                    'name' => $schoolName,
                    'plan_id' => $plan->id,
                    'subscription_status' => $status,
                    'expires_at' => $endsAt,
                ]);

                $domain = $tenant->createDomain(['domain' => $domainName]);
                $createdDomainId = $domain->id;

                $subscription = Subscription::create([
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                    'status' => $status,
                    'trial_starts_at' => $isTrial ? $startsAt : null,
                    'trial_ends_at' => $isTrial ? $endsAt : null,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                ]);

                $planPrice = $billingInterval === 'yearly'
                    ? $plan->yearly_price
                    : ($plan->termly_price ?? $plan->monthly_price);
                $amount = $isTrial ? 0.00 : $planPrice;

                Invoice::create([
                    'tenant_id' => $tenant->id,
                    'subscription_id' => $subscription->id,
                    'invoice_number' => 'INV-' . strtoupper(uniqid()),
                    'amount' => $amount,
                    'status' => $isTrial ? 'paid' : 'unpaid',
                    'due_date' => now(),
                    'paid_at' => $isTrial ? now() : null,
                ]);

                SubscriptionLog::log(
                    $tenant->id,
                    $isTrial ? 'trial_started' : 'subscription_activated',
                    "School {$schoolName} provisioned successfully with plan {$plan->name}.",
                    ['payment_ref' => $paymentRef],
                );

                return $tenant;
            });

            // The Tenant model's TenantCreated event triggers Stancl's
            // CreateDatabase + MigrateDatabase + SeedDatabase pipeline
            // (currently synchronous; Batch 3 wires the production seeder
            // and Week 2 flips it to queued). By the time we get here the
            // tenant DB exists and is ready for the owner user insert.
            $this->createOwnerUser($tenant, $ownerName, $ownerEmail, $hashedPassword);

            event(new SubscriptionLifecycleEvent(
                $tenant,
                $isTrial ? 'trial_started' : 'school_created',
            ));

            return $tenant;

        } catch (\Throwable $e) {
            // Rollback everything we managed to create on the way in so a
            // failed provisioning never leaves a half-built tenant behind:
            //   - Delete the tenant row → cascades subscription/invoice/domain
            //     via FK, and fires Stancl's TenantDeleted pipeline which
            //     drops the tenant DB and its storage directories.
            //   - If we never got as far as creating the tenant, there's
            //     nothing to clean up centrally.
            if ($tenant) {
                try {
                    $tenant->delete();
                } catch (\Throwable $cleanupError) {
                    // Cleanup failure is logged loudly but MUST NOT mask the
                    // original error — that's what the operator needs to see.
                    Log::critical('Tenant rollback failed after provisioning error.', [
                        'tenant_id' => $tenant->id,
                        'original_error' => $e->getMessage(),
                        'cleanup_error' => $cleanupError->getMessage(),
                    ]);
                }
            }

            Log::error('Tenant provisioning failed and was rolled back.', [
                'subdomain' => $subdomain,
                'school_name' => $schoolName,
                'payment_ref' => $paymentRef,
                'error' => $e->getMessage(),
            ]);

            // Re-throw so callers (webhook controller, onboarding controller)
            // see the failure and can respond appropriately. Silently returning
            // a broken tenant is the bug this whole refactor exists to kill.
            throw $e;
        }
    }

    /**
     * Insert the school owner as a super_admin in the tenant DB.
     *
     * If this fails (missing FK targets in the seeded tenant DB, unique
     * clash on email, whatever), we throw — the outer catch above rolls
     * back the tenant, so we never leave a school with no way in.
     *
     * Placeholder values for state/lga/nationality/gender/religion are
     * intentional short-term compromises; they let existing seeder rows
     * satisfy the not-null FKs. The onboarding wizard will collect these
     * from the user in a later pass and pass them in explicitly.
     */
    protected function createOwnerUser(
        Tenant $tenant,
        string $ownerName,
        string $ownerEmail,
        string $hashedPassword,
    ): void {
        tenancy()->initialize($tenant);

        try {
            User::create([
                'name' => $ownerName,
                'email' => $ownerEmail,
                'username' => explode('@', $ownerEmail)[0],
                'password' => $hashedPassword,  // Already bcrypt; NOT re-hashed.
                'user_type' => 'super_admin',
                'blocked' => 0,
                'religion' => null,
                'gender' => null,
                'dob' => null,
                'photo' => 'global_assets/images/user.png',
                'phone' => null,
                'address' => null,
                'state_id' => 1,   // Must exist in seeded states table.
                'lga_id' => 1,     // Must exist in seeded lgas table.
                'nal_id' => 1,     // Must exist in seeded nationalities table.
            ]);
        } finally {
            // Always end tenancy, even if User::create threw — otherwise
            // the caller's outer transaction / cleanup runs against the
            // WRONG connection and starts corrupting the tenant DB.
            tenancy()->end();
        }
    }
}
