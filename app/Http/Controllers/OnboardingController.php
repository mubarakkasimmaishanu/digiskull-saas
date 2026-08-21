<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\PendingRegistration;
use App\Services\TenantProvisionService;
use App\Helpers\Qs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class OnboardingController extends Controller
{
    protected $provisionService;

    public function __construct(TenantProvisionService $provisionService)
    {
        $this->provisionService = $provisionService;
    }

    public function checkSubdomain(Request $request)
    {
        $subdomain = strtolower($request->query('subdomain'));
        
        if (!preg_match('/^[a-z0-9\-]+$/', $subdomain)) {
            return response()->json(['available' => false, 'message' => 'Invalid characters. Use only letters, numbers, and hyphens.']);
        }

        // Exclude system names
        $reserved = ['www', 'admin', 'portal', 'api', 'saas', 'billing', 'mail', 'itguy', 'central', 'localhost', '127.0.0.1'];
        if (in_array($subdomain, $reserved)) {
            return response()->json(['available' => false, 'message' => 'This subdomain is reserved.']);
        }

        $host = request()->getHost();
        // Suffix mapping
        $domainToCheck = $subdomain . '.' . $host;

        $exists = DB::table('domains')->where('domain', $domainToCheck)->exists();

        return response()->json(['available' => !$exists]);
    }

    public function wizard(Request $request)
    {
        $plans = Plan::where('active', true)->orderBy('sort_order')->get();
        $selectedPlan = $request->query('plan', 'standard');
        $initialSubdomain = $request->query('subdomain');
        $billingCycle = $request->query('cycle', 'termly');

        return view('saas.onboard_wizard', compact('plans', 'selectedPlan', 'initialSubdomain', 'billingCycle'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'plan_slug' => 'required|exists:plans,slug',
            'subdomain' => 'required|string|max:100',
            'school_name' => 'required|string|max:255',
            'school_email' => 'required|email|max:255',
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|max:255',
            'owner_password' => 'required|string|min:8',
            'billing_interval' => 'required|in:termly,yearly,monthly',
            'payment_method' => 'required|string',
        ]);

        $subdomain = strtolower($request->subdomain);
        $host = request()->getHost();
        $domainName = $subdomain . '.' . $host;

        // Double check domain availability
        if (DB::table('domains')->where('domain', $domainName)->exists()) {
            return back()->withErrors(['subdomain' => 'The chosen subdomain is already taken.'])->withInput();
        }

        $plan = Plan::where('slug', $request->plan_slug)->firstOrFail();

        // Normalise monthly to termly
        $billingInterval = $request->billing_interval === 'monthly' ? 'termly' : $request->billing_interval;

        // Hash the owner password IMMEDIATELY on capture. It's never stored plaintext.
        $hashedPassword = Hash::make($request->owner_password);

        $isTrialMethod = in_array($request->payment_method, ['trial', 'trial_card', 'national', 'international']);

        if ($isTrialMethod) {
            // Payment auth token (last 4 digits / simulation token for automated billing)
            $cardNumber = preg_replace('/\D/', '', $request->input('card_number', ''));
            $last4 = strlen($cardNumber) >= 4 ? substr($cardNumber, -4) : '4242';
            $authRef = 'AUTH-TRM-' . strtoupper(uniqid()) . '-L4' . $last4;

            // Provision 1-Term Free Trial (90 days) immediately
            $tenant = $this->provisionService->provision(
                $plan,
                $subdomain,
                $request->school_name,
                $request->school_email,
                $request->owner_name,
                $request->owner_email,
                $hashedPassword,
                $billingInterval,
                true, // isTrial = 1 Full Term Free (90 days)
                $authRef
            );

            // Redirect to success page with hashed tenant ID
            $hashedId = Qs::hash($tenant->id);
            return redirect()->route('onboard.success', ['tenant_hash' => $hashedId])
                             ->with('pop_success', '1-Term Free Trial activated! School portal provisioned successfully.');
        } else {
            // Direct paid billing checkout: Create pending registration
            $reference = 'DIGI-' . strtoupper(uniqid());
            $amount = $billingInterval === 'yearly' 
                ? $plan->yearly_price 
                : ($plan->termly_price ?? $plan->monthly_price);

            $pending = PendingRegistration::create([
                'reference' => $reference,
                'plan_id' => $plan->id,
                'billing_interval' => $billingInterval,
                'subdomain' => $subdomain,
                'school_name' => $request->school_name,
                'school_email' => $request->school_email,
                'owner_name' => $request->owner_name,
                'owner_email' => $request->owner_email,
                'owner_password' => $hashedPassword,
                'amount' => $amount,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
            ]);

            return redirect()->route('onboard.payment', ['tenant_hash' => Qs::hash($pending->id)]);
        }
    }

    public function paymentPage($tenant_hash)
    {
        $pendingId = Qs::decodeHash($tenant_hash);
        $pending = PendingRegistration::with('plan')->findOrFail($pendingId);

        return view('saas.payment', compact('pending', 'tenant_hash'));
    }

    public function processTrial($tenant_hash)
    {
        $pendingId = Qs::decodeHash($tenant_hash);
        $pending = PendingRegistration::with('plan')->findOrFail($pendingId);

        // Provision 1-term trial using details from pending registration
        $tenant = $this->provisionService->provision(
            $pending->plan,
            $pending->subdomain,
            $pending->school_name,
            $pending->school_email,
            $pending->owner_name,
            $pending->owner_email,
            $pending->owner_password,
            $pending->billing_interval ?: 'termly',
            true // isTrial = 1 Full Term Free
        );

        // Delete pending registration after successful conversion to trial
        $pending->delete();

        // Redirect to success page with hashed tenant ID
        $hashedId = Qs::hash($tenant->id);
        return redirect()->route('onboard.success', ['tenant_hash' => $hashedId])
                         ->with('pop_success', '1-Term Free Trial started successfully!');
    }

    public function successPage($tenant_hash)
    {
        $tenantId = Qs::decodeHash($tenant_hash);
        $tenant = Tenant::with('domain')->findOrFail($tenantId);

        return view('saas.success', compact('tenant'));
    }
}
