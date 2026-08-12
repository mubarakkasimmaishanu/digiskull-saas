<?php

namespace App\Http\Controllers\Api;

use App\Models\Plan;
use App\Models\PendingRegistration;
use App\Models\Tenant;
use App\Models\Subscription;
use App\Models\Invoice;
use App\Models\SubscriptionPayment;
use App\Services\Payments\FlutterwaveGateway;
use App\Services\Payments\PaystackGateway;
use App\Services\TenantProvisionService;
use App\Helpers\Qs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class ApiOnboardingController extends ApiBaseController
{
    public function __construct(
        protected TenantProvisionService $provisionService,
        protected PaystackGateway $paystack,
        protected FlutterwaveGateway $flutterwave,
    ) {}

    /**
     * Get list of active subscription plans.
     */
    public function plans(): JsonResponse
    {
        try {
            $plans = Plan::where('active', true)->orderBy('sort_order')->get();
            return $this->sendResponse($plans, 'Plans retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to fetch plans.', ['error' => [$e->getMessage()]], 500);
        }
    }

    /**
     * Live Subdomain availability check.
     */
    public function checkSubdomain(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subdomain' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        $subdomain = strtolower($request->query('subdomain'));
        
        if (!preg_match('/^[a-z0-9\-]+$/', $subdomain)) {
            return $this->sendResponse(['available' => false], 'Invalid characters. Use only letters, numbers, and hyphens.');
        }

        $reserved = ['www', 'admin', 'portal', 'api', 'saas', 'billing', 'mail', 'itguy', 'central', 'localhost', '127.0.0.1'];
        if (in_array($subdomain, $reserved)) {
            return $this->sendResponse(['available' => false], 'This subdomain is reserved.');
        }

        $host = request()->getHost();
        $domainToCheck = $subdomain . '.' . $host;

        $exists = DB::table('domains')->where('domain', $domainToCheck)->exists();

        return $this->sendResponse(
            ['available' => !$exists],
            !$exists ? 'Subdomain is available.' : 'Subdomain is already taken.'
        );
    }

    /**
     * Submit onboarding registration.
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'plan_slug' => 'required|exists:plans,slug',
            'subdomain' => 'required|string|max:100',
            'school_name' => 'required|string|max:255',
            'school_email' => 'required|email|max:255',
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|max:255',
            'owner_password' => 'required|string|min:8',
            'billing_interval' => 'required|in:monthly,yearly',
            'payment_method' => 'required|in:trial,national,international',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        $subdomain = strtolower($request->subdomain);
        $host = request()->getHost();
        $domainName = $subdomain . '.' . $host;

        // Double check domain availability
        if (DB::table('domains')->where('domain', $domainName)->exists()) {
            return $this->sendError('Subdomain is already taken.', ['subdomain' => ['The chosen subdomain is already taken.']], 422);
        }

        $plan = Plan::where('slug', $request->plan_slug)->first();

        // Hash the owner password immediately. Never store or forward plaintext.
        $hashedPassword = Hash::make($request->owner_password);

        if ($request->payment_method === 'trial') {
            try {
                $tenant = $this->provisionService->provision(
                    $plan,
                    $subdomain,
                    $request->school_name,
                    $request->school_email,
                    $request->owner_name,
                    $request->owner_email,
                    $hashedPassword,
                    $request->billing_interval,
                    true // isTrial = true
                );

                $domain = $tenant->domains()->first();
                $finalDomain = $domain ? $domain->domain : $domainName;

                return $this->sendResponse([
                    'provisioned' => true,
                    'tenant_id' => $tenant->id,
                    'subdomain' => $subdomain,
                    'domain' => $finalDomain,
                    'redirect_url' => 'http://' . $finalDomain . '/login'
                ], 'School trial workspace provisioned successfully.', 201);

            } catch (\Exception $e) {
                return $this->sendError('Workspace provisioning failed.', ['exception' => [$e->getMessage()]], 500);
            }
        } else {
            // Paid onboarding: Create pending registration
            $reference = 'EDUOS-' . strtoupper(uniqid());
            $amount = $request->billing_interval === 'yearly' ? $plan->yearly_price : $plan->monthly_price;

            try {
                $pending = PendingRegistration::create([
                    'reference' => $reference,
                    'plan_id' => $plan->id,
                    'billing_interval' => $request->billing_interval,
                    'subdomain' => $subdomain,
                    'school_name' => $request->school_name,
                    'school_email' => $request->school_email,
                    'owner_name' => $request->owner_name,
                    'owner_email' => $request->owner_email,
                    'owner_password' => $hashedPassword, // Bcrypt hash, never the plaintext.
                    'amount' => $amount,
                    'payment_method' => $request->payment_method,
                    'status' => 'pending',
                ]);

                $tenant_hash = Qs::hash($pending->id);

                // Set gateway keys. Read from config so a missing env value
                // surfaces as a clear "gateway_misconfigured" error to the
                // mobile client instead of silently downgrading to a hardcoded
                // test key (the old behaviour, which caused real charges to
                // fail in prod with no diagnostic).
                $publicKey = $request->payment_method === 'national'
                    ? config('services.paystack.public_key')
                    : config('services.flutterwave.public_key');

                if (empty($publicKey)) {
                    Log::error('Onboarding checkout attempted but public key is not configured.', [
                        'payment_method' => $request->payment_method,
                    ]);
                    return $this->sendError('Gateway not configured.', ['gateway' => ['Contact support.']], 503);
                }

                return $this->sendResponse([
                    'provisioned' => false,
                    'pending_id' => $pending->id,
                    'tenant_hash' => $tenant_hash,
                    'reference' => $reference,
                    'amount' => $amount,
                    'payment_method' => $request->payment_method,
                    'payment_url' => route('onboard.payment', ['tenant_hash' => $tenant_hash]),
                    'checkout_details' => [
                        'email' => $request->owner_email,
                        'name' => $request->owner_name,
                        'amount' => $amount,
                        'currency' => 'NGN',
                        'reference' => $reference,
                        'public_key' => $publicKey,
                        'gateway' => $request->payment_method === 'national' ? 'paystack' : 'flutterwave'
                    ]
                ], 'Registration pending. Initialize checkout in the mobile app.');

            } catch (\Exception $e) {
                return $this->sendError('Pending registration failed to create.', ['exception' => [$e->getMessage()]], 500);
            }
        }
    }

    /**
     * Verify a client-reported payment reference and, if it verifies with the
     * gateway, provision the tenant.
     *
     * This endpoint is called by the mobile app after the user completes
     * checkout. It CANNOT trust the client — the reference is passed by
     * the client, so the gateway itself must confirm the payment happened
     * (and for the expected amount) before we do anything. Same rules as
     * the webhook path in PaymentWebhookController.
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reference' => 'required|string',
            'gateway' => 'required|in:paystack,flutterwave',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        $reference = $request->reference;
        $gateway = $request->gateway;

        // Idempotency: if this reference has already been successfully
        // processed we short-circuit (a mobile retry, a webhook that already
        // fired, etc.). Return 200 so the client stops asking.
        if (SubscriptionPayment::where('reference', $reference)
            ->where('status', 'successful')
            ->exists()) {
            return $this->sendResponse(['already_processed' => true], 'Payment previously verified.');
        }

        $pending = PendingRegistration::where('reference', $reference)
                                     ->where('status', 'pending')
                                     ->first();

        if (!$pending) {
            return $this->sendError('Pending registration not found or already processed.', [], 404);
        }

        // Verify the payment with the gateway itself. Client-supplied
        // reference is metadata; the gateway's own API is the source of truth.
        $verification = $gateway === 'paystack'
            ? $this->paystack->verify($reference)
            : $this->flutterwave->verify($reference);

        if (!$verification->successful) {
            Log::warning('Mobile verifyPayment: gateway rejected reference.', [
                'reference' => $reference,
                'gateway' => $gateway,
                'reason' => $verification->failureReason,
            ]);
            return $this->sendError('Payment not verified by gateway.', ['reason' => [$verification->failureReason]], 422);
        }

        // Amount check: never provision if the gateway says less was paid
        // than we quoted. Compare in minor units (kobo/cents) to avoid
        // float traps.
        $expectedMinor = (int) round(((float) $pending->amount) * 100);
        if ($verification->amountMinor < $expectedMinor) {
            Log::warning('Mobile verifyPayment: amount mismatch.', [
                'reference' => $reference,
                'expected_minor' => $expectedMinor,
                'paid_minor' => $verification->amountMinor,
            ]);
            return $this->sendError('Amount mismatch.', [], 422);
        }

        try {
            $tenant = DB::transaction(function () use ($pending, $gateway, $verification, $reference) {
                $tenant = $this->provisionService->provision(
                    plan: $pending->plan,
                    subdomain: $pending->subdomain,
                    schoolName: $pending->school_name,
                    schoolEmail: $pending->school_email,
                    ownerName: $pending->owner_name,
                    ownerEmail: $pending->owner_email,
                    hashedPassword: $pending->owner_password, // already bcrypt (Batch 2).
                    billingInterval: $pending->billing_interval,
                    isTrial: false,
                    paymentRef: $reference,
                );

                $pending->update(['status' => 'completed']);

                $subscription = Subscription::where('tenant_id', $tenant->id)
                    ->orderByDesc('created_at')->first();
                $invoice = Invoice::where('tenant_id', $tenant->id)
                    ->orderByDesc('created_at')->first();

                if ($invoice) {
                    $invoice->update(['status' => 'paid', 'paid_at' => now()]);
                }

                // Unique index on `reference` makes concurrent duplicates
                // throw QueryException — the outer catch turns that into
                // the same "already_processed" success response.
                SubscriptionPayment::create([
                    'subscription_id' => $subscription?->id,
                    'invoice_id' => $invoice?->id,
                    'amount' => $verification->amountMinor / 100,
                    'gateway' => $gateway,
                    'reference' => $reference,
                    'status' => 'successful',
                    'meta' => [
                        'source' => 'api_verify_payment',
                        'gateway_status' => $verification->raw['data']['status'] ?? null,
                        'currency' => $verification->currency,
                    ],
                ]);

                return $tenant;
            });
        } catch (\Illuminate\Database\QueryException $e) {
            $code = $e->errorInfo[1] ?? null;
            if ($code === 1062 || $e->getCode() === '23000') {
                return $this->sendResponse(['already_processed' => true], 'Payment previously verified.');
            }
            Log::error('Mobile verifyPayment DB error.', ['reference' => $reference, 'error' => $e->getMessage()]);
            return $this->sendError('Provisioning failed.', ['exception' => [$e->getMessage()]], 500);
        } catch (\Throwable $e) {
            Log::error('Mobile verifyPayment failed.', ['reference' => $reference, 'error' => $e->getMessage()]);
            return $this->sendError('Provisioning failed during verification.', ['exception' => [$e->getMessage()]], 500);
        }

        $domain = $tenant->domains()->first();
        $domainName = $domain ? $domain->domain : ($pending->subdomain . '.' . (request()?->getHost() ?? config('app.tenant_domain_suffix', 'localhost')));
        $scheme = config('app.tenant_scheme', 'https');

        return $this->sendResponse([
            'provisioned' => true,
            'tenant_id' => $tenant->id,
            'subdomain' => $pending->subdomain,
            'domain' => $domainName,
            'redirect_url' => $scheme . '://' . $domainName . '/login',
        ], 'Workspace provisioned successfully post payment.');
    }

    /**
     * Start free trial from payment fallback.
     */
    public function processTrial(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tenant_hash' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        try {
            $pendingId = Qs::decodeHash($request->tenant_hash);
            $pending = PendingRegistration::with('plan')->findOrFail($pendingId);

            $tenant = $this->provisionService->provision(
                $pending->plan,
                $pending->subdomain,
                $pending->school_name,
                $pending->school_email,
                $pending->owner_name,
                $pending->owner_email,
                $pending->owner_password,
                $pending->billing_interval,
                true // isTrial = true
            );

            $pending->delete();

            $domain = $tenant->domains()->first();
            $domainName = $domain ? $domain->domain : ($pending->subdomain . '.' . (request()?->getHost() ?? config('app.tenant_domain_suffix', 'localhost')));
            $scheme = config('app.tenant_scheme', 'https');

            return $this->sendResponse([
                'provisioned' => true,
                'tenant_id' => $tenant->id,
                'subdomain' => $pending->subdomain,
                'domain' => $domainName,
                'redirect_url' => $scheme . '://' . $domainName . '/login',
            ], 'Workspace converted and provisioned as free trial.');

        } catch (\Exception $e) {
            return $this->sendError('Conversion to trial failed.', ['exception' => [$e->getMessage()]], 500);
        }
    }
}
