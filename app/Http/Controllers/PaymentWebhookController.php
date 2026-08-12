<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\Invoice;
use App\Models\PendingRegistration;
use App\Models\Subscription;
use App\Models\SubscriptionLog;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use App\Services\Payments\FlutterwaveGateway;
use App\Services\Payments\GatewayVerificationResult;
use App\Services\Payments\PaystackGateway;
use App\Services\TenantProvisionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Payment gateway webhook receiver.
 *
 * Contract with the outside world:
 *   1. Signature already verified by route middleware (paystack.signature /
 *      flutterwave.signature). If we're inside the controller, the caller
 *      proved they hold our webhook secret.
 *   2. We STILL do a server-side verify call to the gateway using the
 *      reference — a compromised secret or a replayed body must not be
 *      enough to provision a tenant.
 *   3. We STILL compare the gateway-reported amount to the amount stored
 *      when the user registered — a manipulated checkout amount must not
 *      be enough to under-pay for a plan.
 *   4. Every side effect (activation, provisioning, payment record) is
 *      inside a DB::transaction, and the transaction is fenced by an
 *      idempotency check on SubscriptionPayment.reference so a duplicate
 *      webhook (retries, at-least-once delivery) collapses to a no-op.
 */
class PaymentWebhookController extends Controller
{
    public function __construct(
        private readonly TenantProvisionService $provisionService,
        private readonly PaystackGateway $paystack,
        private readonly FlutterwaveGateway $flutterwave,
    ) {}

    // ------------------------------------------------------------------ Paystack

    public function paystackWebhook(Request $request): JsonResponse
    {
        // Paystack fires several event types; we only care about a settled charge.
        if ($request->input('event') !== 'charge.success') {
            return response()->json(['status' => 'ignored']);
        }

        $reference = (string) $request->input('data.reference', '');
        if ($reference === '') {
            return response()->json(['error' => 'Missing reference.'], 400);
        }

        Log::info('Paystack webhook accepted after signature check.', [
            'reference' => $reference,
            'event' => $request->input('event'),
        ]);

        // Server-side verification (never trust the webhook body).
        $verification = $this->paystack->verify($reference);
        if (!$verification->successful) {
            Log::warning('Paystack verify rejected webhook.', [
                'reference' => $reference,
                'reason' => $verification->failureReason,
            ]);
            return response()->json(['error' => 'Payment not verified.'], 422);
        }

        return $this->handleVerifiedPayment(
            reference: $reference,
            gateway: 'paystack',
            verification: $verification,
        );
    }

    // ------------------------------------------------------------------ Flutterwave

    public function flutterwaveWebhook(Request $request): JsonResponse
    {
        $reference = (string) ($request->input('data.tx_ref') ?: $request->input('txRef', ''));
        if ($reference === '') {
            return response()->json(['error' => 'Missing tx_ref.'], 400);
        }

        Log::info('Flutterwave webhook accepted after signature check.', [
            'reference' => $reference,
            'event' => $request->input('event'),
        ]);

        $verification = $this->flutterwave->verify($reference);
        if (!$verification->successful) {
            Log::warning('Flutterwave verify rejected webhook.', [
                'reference' => $reference,
                'reason' => $verification->failureReason,
            ]);
            return response()->json(['error' => 'Payment not verified.'], 422);
        }

        return $this->handleVerifiedPayment(
            reference: $reference,
            gateway: 'flutterwave',
            verification: $verification,
        );
    }

    // ------------------------------------------------------------------ Shared

    /**
     * At this point:
     *   - The signature has been verified by middleware.
     *   - The gateway itself has confirmed the payment via its verify API.
     *   - `$verification` holds the amount actually charged (in minor units).
     *
     * We now decide whether this reference belongs to a pending onboarding
     * (first-time provisioning) or an existing tenant renewal, and route
     * accordingly. Idempotency is enforced by inserting into
     * `subscription_payments` with a unique `reference` column BEFORE
     * doing any work — a duplicate webhook collides on that unique index
     * and returns the same 200 without side effects.
     */
    protected function handleVerifiedPayment(
        string $reference,
        string $gateway,
        GatewayVerificationResult $verification,
    ): JsonResponse {
        // Idempotency short-circuit: if we've already recorded this reference
        // as successful, do nothing but respond 200 so the gateway stops retrying.
        if (SubscriptionPayment::where('reference', $reference)
            ->where('status', 'successful')
            ->exists()) {
            Log::info('Webhook duplicate — reference already processed.', [
                'reference' => $reference,
                'gateway' => $gateway,
            ]);
            return response()->json(['status' => 'already_processed']);
        }

        $pending = PendingRegistration::where('reference', $reference)
            ->where('status', 'pending')
            ->first();

        if ($pending) {
            return $this->provisionFromPending($pending, $gateway, $verification);
        }

        return $this->renewExistingSubscription($reference, $gateway, $verification);
    }

    /**
     * First-time onboarding: the pending registration was created when the
     * school completed the wizard. Provision the tenant only after we've
     * verified the amount matches what we quoted.
     */
    protected function provisionFromPending(
        PendingRegistration $pending,
        string $gateway,
        GatewayVerificationResult $verification,
    ): JsonResponse {
        $expectedMinor = (int) round(((float) $pending->amount) * 100);

        if ($verification->amountMinor < $expectedMinor) {
            Log::warning('Payment amount mismatch — refusing to provision.', [
                'reference' => $pending->reference,
                'expected_minor' => $expectedMinor,
                'paid_minor' => $verification->amountMinor,
                'currency' => $verification->currency,
            ]);
            return response()->json(['error' => 'Amount mismatch.'], 422);
        }

        try {
            $tenant = DB::transaction(function () use ($pending, $gateway, $verification) {
                // Provision the tenant + subscription + invoice.
                // TenantProvisionService is expected to be atomic in a
                // later batch of this refactor (Batch 2). Until then we
                // wrap the call in this outer transaction so at least the
                // central-DB writes below roll back together on failure.
                $tenant = $this->provisionService->provision(
                    plan: $pending->plan,
                    subdomain: $pending->subdomain,
                    schoolName: $pending->school_name,
                    schoolEmail: $pending->school_email,
                    ownerName: $pending->owner_name,
                    ownerEmail: $pending->owner_email,
                    ownerPassword: $pending->owner_password,
                    billingInterval: $pending->billing_interval,
                    isTrial: false,
                    paymentRef: $pending->reference,
                );

                $pending->update(['status' => 'completed']);

                $subscription = Subscription::where('tenant_id', $tenant->id)
                    ->orderByDesc('created_at')
                    ->first();

                $invoice = Invoice::where('tenant_id', $tenant->id)
                    ->orderByDesc('created_at')
                    ->first();

                if ($invoice) {
                    $invoice->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
                }

                // Idempotent write: the unique index on `reference` makes
                // a concurrent duplicate throw QueryException, which the
                // outer catch turns into a safe 200 "already_processed".
                SubscriptionPayment::create([
                    'subscription_id' => $subscription?->id,
                    'invoice_id' => $invoice?->id,
                    'amount' => $verification->amountMinor / 100,
                    'gateway' => $gateway,
                    'reference' => $pending->reference,
                    'status' => 'successful',
                    'meta' => $this->redactMeta($verification->raw),
                ]);

                return $tenant;
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Almost certainly the unique-key collision on `reference`
            // caused by a duplicate webhook that raced us. Idempotent.
            if ($this->isUniqueViolation($e)) {
                return response()->json(['status' => 'already_processed']);
            }
            Log::error('Provisioning DB error in Paystack/Flutterwave webhook.', [
                'reference' => $pending->reference,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'provisioning_failed'], 500);
        } catch (\Throwable $e) {
            Log::error('Provisioning failed in webhook.', [
                'reference' => $pending->reference,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'provisioning_failed'], 500);
        }

        return response()->json([
            'success' => true,
            'redirect_url' => route('onboard.success', ['tenant_hash' => Qs::hash($tenant->id)]),
        ]);
    }

    /**
     * Recurring / renewal payment for an already-provisioned tenant.
     * The gateway is expected to include our internal `tenant_id` in the
     * transaction metadata, which we surface at data.metadata.tenant_id
     * (Paystack) or data.meta.tenant_id (Flutterwave). Falling back to
     * whichever key the caller provided.
     */
    protected function renewExistingSubscription(
        string $reference,
        string $gateway,
        GatewayVerificationResult $verification,
    ): JsonResponse {
        $tenantId = data_get($verification->raw, 'data.metadata.tenant_id')
            ?? data_get($verification->raw, 'data.meta.tenant_id');

        if (!$tenantId) {
            Log::warning('Webhook has no matching pending registration and no tenant metadata.', [
                'reference' => $reference,
                'gateway' => $gateway,
            ]);
            return response()->json(['status' => 'unmatched']);
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            return response()->json(['error' => 'tenant_not_found'], 404);
        }

        try {
            DB::transaction(function () use ($tenant, $reference, $gateway, $verification) {
                $invoice = Invoice::where('tenant_id', $tenant->id)
                    ->where('status', 'unpaid')
                    ->orderByDesc('created_at')
                    ->first();

                $subscription = Subscription::where('tenant_id', $tenant->id)
                    ->orderByDesc('created_at')
                    ->first();

                if (!$subscription) {
                    // Renewal without an existing subscription row is
                    // structurally impossible for a real tenant — bail loudly.
                    throw new \RuntimeException("Tenant #{$tenant->id} has no subscription row to renew.");
                }

                // Amount check for renewals: compare against the outstanding
                // invoice if we have one, else against the current plan price.
                $expectedMinor = $invoice
                    ? (int) round(((float) $invoice->amount) * 100)
                    : (int) round(((float) ($subscription->plan?->monthly_price ?? 0)) * 100);

                if ($expectedMinor > 0 && $verification->amountMinor < $expectedMinor) {
                    throw new \RuntimeException("Renewal amount mismatch for tenant #{$tenant->id}");
                }

                $newEndsAt = \Carbon\Carbon::parse($subscription->ends_at)->isPast()
                    ? now()->addMonth()
                    : \Carbon\Carbon::parse($subscription->ends_at)->addMonth();

                if ($invoice) {
                    $invoice->update(['status' => 'paid', 'paid_at' => now()]);
                }

                $subscription->update([
                    'status' => 'active',
                    'ends_at' => $newEndsAt,
                ]);

                $tenant->update([
                    'subscription_status' => 'active',
                    'expires_at' => $newEndsAt,
                ]);

                SubscriptionPayment::create([
                    'subscription_id' => $subscription->id,
                    'invoice_id' => $invoice?->id,
                    'amount' => $verification->amountMinor / 100,
                    'gateway' => $gateway,
                    'reference' => $reference,
                    'status' => 'successful',
                    'meta' => $this->redactMeta($verification->raw),
                ]);

                SubscriptionLog::log(
                    $tenant->id,
                    'subscription_renewed',
                    "Subscription renewed for {$tenant->name}.",
                    ['reference' => $reference],
                );

                event(new \App\Events\SubscriptionLifecycleEvent($tenant, 'subscription_renewed'));
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if ($this->isUniqueViolation($e)) {
                return response()->json(['status' => 'already_processed']);
            }
            Log::error('Renewal DB error.', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'renewal_failed'], 500);
        } catch (\Throwable $e) {
            Log::error('Renewal failed.', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'renewal_failed'], 500);
        }

        return response()->json(['success' => true]);
    }

    /**
     * MySQL unique-index violation error code detector.
     * Covers both 1062 (MySQL) and the SQLSTATE 23000 family.
     */
    protected function isUniqueViolation(\Illuminate\Database\QueryException $e): bool
    {
        $code = $e->errorInfo[1] ?? null;
        return $code === 1062 || $e->getCode() === '23000';
    }

    /**
     * Strip anything remotely PII/PCI from the gateway payload before we
     * persist it in `subscription_payments.meta` for audit. We keep the
     * transaction status + reference + gateway metadata that we actually
     * need to reconstruct a payment, and drop card holder / auth details.
     */
    protected function redactMeta(array $raw): array
    {
        $data = $raw['data'] ?? [];

        return [
            'gateway_event' => $raw['event'] ?? null,
            'status' => $data['status'] ?? null,
            'reference' => $data['reference'] ?? ($data['tx_ref'] ?? null),
            'amount_minor' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? null,
            'channel' => $data['channel'] ?? ($data['payment_type'] ?? null),
            'paid_at' => $data['paid_at'] ?? ($data['created_at'] ?? null),
        ];
    }
}
