@extends('layouts.saas_master')

@section('page_title', 'Complete Payment')

@section('content')
<div class="container my-5 py-4">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="glass-card p-4 p-md-5 shadow-2xl">
                <div class="text-center mb-4">
                    <span class="badge badge-pill badge-info px-3 py-1 mb-2 font-weight-bold">SECURE CHECKOUT</span>
                    <h3 class="text-white font-weight-bold">Activate School Subscription</h3>
                    <p class="text-muted mb-0">Invoice Reference: <code>{{ $pending->reference }}</code></p>
                </div>

                <div class="text-center">
                    <h5 class="text-white font-weight-bold mb-1">{{ $pending->school_name }}</h5>
                    <p class="text-muted font-size-sm">Selected Tier: <strong class="text-info">{{ $pending->plan->name }} ({{ ucfirst($pending->billing_interval) }})</strong></p>
                    
                    <div class="my-4 p-3 rounded-3" style="background: rgba(14, 165, 233, 0.1); border: 1px solid rgba(14, 165, 233, 0.3);">
                        <span class="h2 text-white font-weight-bold">&#8358;{{ number_format($pending->amount, 2) }}</span>
                    </div>

                    <!-- Payment Route Selection -->
                    <div class="my-4 text-center">
                        @if ($pending->payment_method === 'national')
                            <span class="badge badge-success mb-2 px-3 py-1" style="font-size: 0.9rem;">National Payment</span>
                            <p class="text-muted font-size-sm">Routes through <strong>Paystack</strong> (Nigerian Cards, Bank Transfer, USSD)</p>
                            <input type="hidden" name="gateway" id="gateway_input" value="paystack">
                        @else
                            <span class="badge badge-info mb-2 px-3 py-1" style="font-size: 0.9rem;">International Payment</span>
                            <p class="text-muted font-size-sm">Routes through <strong>Flutterwave</strong> (Global USD &amp; Multi-currency Cards)</p>
                            <input type="hidden" name="gateway" id="gateway_input" value="flutterwave">
                        @endif
                    </div>

                    <button type="button" class="btn btn-gradient-primary btn-lg btn-block py-3 rounded-pill font-weight-bold shadow-lg mt-4" id="btn-pay" onclick="payNow()">
                        <i class="material-symbols-rounded align-middle mr-1">credit_card</i> Complete Payment Now
                    </button>

                    <!-- Trial Fallback option -->
                    <form action="{{ route('onboard.process_trial', ['tenant_hash' => $tenant_hash]) }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="btn btn-outline-glass btn-block btn-sm py-2 rounded-pill">
                            Or Start 14-Day Free Trial Instead
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Paystack and Flutterwave Scripts -->
<script src="https://js.paystack.co/v1/inline.js"></script>
<script src="https://checkout.flutterwave.com/v3.js"></script>

<script>
    function payNow() {
        var gateway = $('#gateway_input').val();
        var email = "{{ $pending->owner_email }}";
        var amount = {{ $pending->amount }};
        var reference = "{{ $pending->reference }}";

        $('#btn-pay').attr('disabled', true).html('<span class="material-symbols-rounded spinner align-middle mr-1">sync</span> Initializing Checkout...');

        if (gateway === 'paystack') {
            payWithPaystack(email, amount, reference);
        } else {
            payWithFlutterwave(email, amount, reference);
        }
    }

    function payWithPaystack(email, amount, reference) {
        var handler = PaystackPop.setup({
            key: "{{ env('PAYSTACK_PUBLIC_KEY', 'pk_test_6cb9ec87db8c028ba313faea2188ffcf769862df') }}", // fallback to sandbox key if env not configured
            email: email,
            amount: amount * 100, // in kobo
            currency: "NGN",
            ref: reference,
            callback: function(response) {
                // Payment completed. Send verification call to trigger tenant creation
                verifyPayment(reference, 'paystack');
            },
            onClose: function() {
                $('#btn-pay').removeAttr('disabled').html('<span class="material-symbols-rounded align-middle mr-1">credit_card</span> Pay Now');
                alert('Payment window closed.');
            }
        });
        handler.openIframe();
    }

    function payWithFlutterwave(email, amount, reference) {
        FlutterwaveCheckout({
            public_key: "{{ env('FLUTTERWAVE_PUBLIC_KEY', 'FLWPUBK_TEST-94ea1c8c56fa90e219ffc6e93e2b2fdf-X') }}",
            tx_ref: reference,
            amount: amount,
            currency: "NGN",
            payment_options: "card, banktransfer, ussd",
            customer: {
                email: email,
                name: "{{ $pending->owner_name }}",
            },
            customizations: {
                title: "Eduos School Management System",
                description: "Subscription Payment",
            },
            callback: function(data) {
                verifyPayment(reference, 'flutterwave');
            },
            onclose: function() {
                $('#btn-pay').removeAttr('disabled').html('<span class="material-symbols-rounded align-middle mr-1">credit_card</span> Pay Now');
            }
        });
    }

    function verifyPayment(reference, gateway) {
        $('#btn-pay').html('<span class="material-symbols-rounded spinner align-middle mr-1">sync</span> Provisioning School Space...');
        
        // We'll perform a request to a verification endpoint which will trigger provision
        $.ajax({
            url: "{{ route('webhook.paystack') }}", // Reuse webhook code or create a verify endpoint
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                event: "charge.success",
                data: {
                    reference: reference,
                    gateway_used: gateway
                }
            },
            success: function(response) {
                if (response.success && response.redirect_url) {
                    window.location.href = response.redirect_url;
                } else {
                    alert('Provisioning failed. Please contact support.');
                    window.location.reload();
                }
            },
            error: function() {
                alert('Connection error. Checking payment status.');
                window.location.reload();
            }
        });
    }
</script>

<style>
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .spinner {
        display: inline-block;
        animation: spin 1.5s linear infinite;
    }
</style>
@endsection
