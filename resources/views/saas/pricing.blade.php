@extends('layouts.saas_master')

@section('page_title', 'Pricing & Subscription Plans')

@section('content')
<div class="container my-5 py-4">
    <div class="text-center mb-4">
        <span class="badge badge-pill badge-info px-3 py-1 mb-2 font-weight-bold">TRANSPARENT BILLING · 1-TERM FREE TRIAL</span>
        <h1 class="text-white font-weight-bold display-4">Predictable Pricing for Modern Schools</h1>
        <p class="lead text-muted max-w-xl mx-auto">
            Enjoy a <strong>1 Full Term Free Trial</strong> (90 days) on all plans. Add payment details now with <strong>&#8358;0 charged today</strong>. Automated termly or yearly billing starts only after your trial period ends.
        </p>

        {{-- Termly vs Yearly Billing Toggle --}}
        <div class="d-inline-flex align-items-center p-1 rounded-pill mt-3 shadow-lg" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255, 255, 255, 0.1);">
            <button type="button" class="btn btn-sm rounded-pill px-4 py-2 text-white font-weight-bold active-toggle-btn" id="btn-toggle-termly" onclick="setBillingCycle('termly')">
                Termly Billing (Per Term)
            </button>
            <button type="button" class="btn btn-sm rounded-pill px-4 py-2 text-muted font-weight-bold" id="btn-toggle-yearly" onclick="setBillingCycle('yearly')">
                Yearly Billing <span class="badge badge-success ml-1 font-size-xs">Save ~20%</span>
            </button>
        </div>
    </div>

    <div class="row justify-content-center pt-3">
        @foreach ($plans as $plan)
            @php
                $termPrice = $plan->termly_price ?: ($plan->monthly_price * 3);
                $yearPrice = $plan->yearly_price;
            @endphp
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="plan-card h-100 p-4 d-flex flex-column justify-content-between position-relative {{ $plan->slug === 'standard' ? 'popular' : '' }}">
                    @if ($plan->slug === 'standard')
                        <div class="position-absolute" style="top: -12px; right: 24px;">
                            <span class="badge badge-info px-3 py-1 shadow font-weight-bold" style="letter-spacing: 0.5px;">MOST POPULAR</span>
                        </div>
                    @endif

                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="text-white font-weight-bold mb-0">{{ $plan->name }}</h4>
                            <span class="badge badge-outline-info font-size-xs px-2 py-1">1 Term Free</span>
                        </div>

                        <div class="mb-4 pb-3 border-bottom border-secondary" style="border-color: rgba(255, 255, 255, 0.08) !important;">
                            {{-- Termly Price Display --}}
                            <div class="price-display price-termly">
                                <span class="h1 text-white font-weight-bold">&#8358;{{ number_format($termPrice) }}</span>
                                <span class="text-muted font-size-sm">/ term</span>
                                <div class="text-muted font-size-xs mt-1">Billed every 4 months (3 terms/session)</div>
                            </div>
                            {{-- Yearly Price Display --}}
                            <div class="price-display price-yearly d-none">
                                <span class="h1 text-white font-weight-bold">&#8358;{{ number_format($yearPrice) }}</span>
                                <span class="text-muted font-size-sm">/ year</span>
                                <div class="text-success font-size-xs mt-1">Billed annually &#183; Best Value for Schools</div>
                            </div>

                            <div class="mt-2 p-2 rounded text-info font-size-xs d-flex align-items-center" style="background: rgba(14, 165, 233, 0.1); border: 1px solid rgba(14, 165, 233, 0.2);">
                                <i class="material-symbols-rounded mr-1 font-size-sm">card_giftcard</i>
                                <span><strong>Due today: &#8358;0.00</strong> &#183; 90-day trial</span>
                            </div>
                        </div>

                        <ul class="list-unstyled mb-4">
                            <li class="d-flex align-items-center mb-3 text-light font-size-sm">
                                <i class="material-symbols-rounded text-success mr-2 font-size-sm">check</i>
                                Up to <strong>{{ $plan->max_students }}</strong> Enrolled Students
                            </li>
                            <li class="d-flex align-items-center mb-3 text-light font-size-sm">
                                <i class="material-symbols-rounded text-success mr-2 font-size-sm">check</i>
                                Up to <strong>{{ $plan->max_staff }}</strong> Teachers &amp; Staff Members
                            </li>
                            <li class="d-flex align-items-center mb-3 text-light font-size-sm">
                                <i class="material-symbols-rounded text-success mr-2 font-size-sm">check</i>
                                Complete Academic Continuous Assessments
                            </li>
                            <li class="d-flex align-items-center mb-3 text-light font-size-sm">
                                <i class="material-symbols-rounded text-success mr-2 font-size-sm">check</i>
                                Printable Official Report Cards &amp; Broad-Sheets
                            </li>
                            <li class="d-flex align-items-center mb-3 text-light font-size-sm">
                                <i class="material-symbols-rounded text-success mr-2 font-size-sm">check</i>
                                Daily Attendance Register &amp; Automated Analytics
                            </li>
                            @if ($plan->slug !== 'basic')
                                <li class="d-flex align-items-center mb-3 text-light font-size-sm">
                                    <i class="material-symbols-rounded text-success mr-2 font-size-sm">check</i>
                                    Bursary, Invoices &amp; Report Card Lock
                                </li>
                                <li class="d-flex align-items-center mb-3 text-light font-size-sm">
                                    <i class="material-symbols-rounded text-success mr-2 font-size-sm">check</i>
                                    Guardian SMS Reminders &amp; Broadcasts
                                </li>
                            @endif
                            @if ($plan->slug === 'premium')
                                <li class="d-flex align-items-center mb-3 text-light font-size-sm">
                                    <i class="material-symbols-rounded text-success mr-2 font-size-sm">check</i>
                                    Priority 24/7 Dedicated Support
                                </li>
                            @endif
                        </ul>
                    </div>

                    <a href="{{ route('onboard.wizard', ['plan' => $plan->slug, 'cycle' => 'termly']) }}" class="btn plan-cta-btn {{ $plan->slug === 'standard' ? 'btn-gradient-primary' : 'btn-outline-glass' }} btn-block py-2 rounded-pill font-weight-bold" data-plan="{{ $plan->slug }}">
                        Start 1-Term Free Trial <i class="material-symbols-rounded align-middle ml-1 font-size-sm">arrow_forward</i>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>

<style>
    .active-toggle-btn {
        background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.35);
    }
    .badge-outline-info {
        background: rgba(14, 165, 233, 0.15);
        color: #38bdf8;
        border: 1px solid rgba(14, 165, 233, 0.3);
    }
</style>

<script>
    let currentCycle = 'termly';

    function setBillingCycle(cycle) {
        currentCycle = cycle;
        if (cycle === 'termly') {
            $('#btn-toggle-termly').addClass('active-toggle-btn text-white').removeClass('text-muted');
            $('#btn-toggle-yearly').removeClass('active-toggle-btn text-white').addClass('text-muted');
            $('.price-termly').removeClass('d-none');
            $('.price-yearly').addClass('d-none');
        } else {
            $('#btn-toggle-yearly').addClass('active-toggle-btn text-white').removeClass('text-muted');
            $('#btn-toggle-termly').removeClass('active-toggle-btn text-white').addClass('text-muted');
            $('.price-yearly').removeClass('d-none');
            $('.price-termly').addClass('d-none');
        }

        // Update CTA links
        $('.plan-cta-btn').each(function() {
            const planSlug = $(this).data('plan');
            $(this).attr('href', "{{ url('/onboard') }}?plan=" + planSlug + "&cycle=" + currentCycle);
        });
    }
</script>
@endsection
