@extends('layouts.saas_master')

@section('page_title', 'School Onboarding & 1-Term Free Trial')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            {{-- Stepper Progress Bar --}}
            <div class="glass-card mb-4 p-3 shadow-lg">
                <div class="d-flex justify-content-between align-items-center flex-wrap" id="steps-indicator">
                    <div class="step-indicator active py-2 px-3 rounded-pill" data-step="1">
                        <span class="badge badge-pill badge-info mr-1">1</span> Select Plan
                    </div>
                    <div class="step-indicator text-muted py-2 px-3 rounded-pill" data-step="2">
                        <span class="badge badge-pill badge-secondary mr-1">2</span> Subdomain
                    </div>
                    <div class="step-indicator text-muted py-2 px-3 rounded-pill" data-step="3">
                        <span class="badge badge-pill badge-secondary mr-1">3</span> School Info
                    </div>
                    <div class="step-indicator text-muted py-2 px-3 rounded-pill" data-step="4">
                        <span class="badge badge-pill badge-secondary mr-1">4</span> Admin Account
                    </div>
                    <div class="step-indicator text-muted py-2 px-3 rounded-pill" data-step="5">
                        <span class="badge badge-pill badge-secondary mr-1">5</span> Payment Details
                    </div>
                </div>
            </div>

            {{-- Wizard Form --}}
            <form id="onboardForm" action="{{ route('onboard.register') }}" method="POST" novalidate>
                @csrf
                
                {{-- STEP 1: SELECT PLAN & BILLING CYCLE --}}
                <div class="setup-step" id="step-1">
                    <div class="glass-card p-4 p-md-5">
                        <div class="text-center mb-4">
                            <span class="badge badge-pill badge-info px-3 py-1 mb-2">STEP 1 OF 5</span>
                            <h3 class="text-white font-weight-bold">Select School Plan &amp; Billing Cycle</h3>
                            <p class="text-muted mb-3">All plans include a <strong>1 Full Term Free Trial (90 Days)</strong> with &#8358;0 charged today.</p>

                            {{-- Termly vs Yearly Switcher --}}
                            <div class="d-inline-flex align-items-center p-1 rounded-pill shadow" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255, 255, 255, 0.1);">
                                <button type="button" class="btn btn-sm rounded-pill px-4 py-2 text-white font-weight-bold active-toggle-btn" id="btn-wiz-termly" onclick="setWizardCycle('termly')">
                                    Termly (Per Term)
                                </button>
                                <button type="button" class="btn btn-sm rounded-pill px-4 py-2 text-muted font-weight-bold" id="btn-wiz-yearly" onclick="setWizardCycle('yearly')">
                                    Yearly <span class="badge badge-success ml-1 font-size-xs">Save ~20%</span>
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            @foreach ($plans as $plan)
                                @php
                                    $termPrice = $plan->termly_price ?: ($plan->monthly_price * 3);
                                    $yearPrice = $plan->yearly_price;
                                    $isSel = ($selectedPlan === $plan->slug) || (!$selectedPlan && $plan->slug === 'standard');
                                @endphp
                                <div class="col-md-4 mb-3">
                                    <div class="plan-card p-4 h-100 cursor-pointer plan-selector-card {{ $isSel ? 'popular border-info' : '' }}" 
                                         style="cursor: pointer;" 
                                         data-slug="{{ $plan->slug }}"
                                         data-termly="{{ $termPrice }}"
                                         data-yearly="{{ $yearPrice }}"
                                         data-name="{{ $plan->name }}"
                                         onclick="selectWizardPlan('{{ $plan->slug }}', this)">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5 class="text-white font-weight-bold mb-0">{{ $plan->name }}</h5>
                                            @if ($plan->slug === 'standard')
                                                <span class="badge badge-info font-size-xs">RECOMMENDED</span>
                                            @else
                                                <span class="badge badge-outline-info font-size-xs">1 Term Free</span>
                                            @endif
                                        </div>
                                        <div class="my-3">
                                            <div class="wiz-price-termly {{ $billingCycle === 'yearly' ? 'd-none' : '' }}">
                                                <span class="h3 text-info font-weight-bold">&#8358;{{ number_format($termPrice) }}</span>
                                                <span class="text-muted font-size-xs">/ term</span>
                                            </div>
                                            <div class="wiz-price-yearly {{ $billingCycle === 'yearly' ? '' : 'd-none' }}">
                                                <span class="h3 text-info font-weight-bold">&#8358;{{ number_format($yearPrice) }}</span>
                                                <span class="text-muted font-size-xs">/ year</span>
                                            </div>
                                            <small class="text-success d-block font-size-xs mt-1">&#8358;0.00 charged today</small>
                                        </div>
                                        <ul class="list-unstyled text-muted small mb-0">
                                            <li class="mb-2"><i class="material-symbols-rounded text-success align-middle font-size-xs mr-1">check</i> Up to <strong>{{ $plan->max_students }}</strong> students</li>
                                            <li class="mb-2"><i class="material-symbols-rounded text-success align-middle font-size-xs mr-1">check</i> Up to <strong>{{ $plan->max_staff }}</strong> teachers</li>
                                            <li class="mb-2"><i class="material-symbols-rounded text-success align-middle font-size-xs mr-1">check</i> 90-day 1-term trial period</li>
                                            <li><i class="material-symbols-rounded text-success align-middle font-size-xs mr-1">check</i> All core ERP modules</li>
                                        </ul>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <input type="hidden" name="plan_slug" id="plan_slug" value="{{ $selectedPlan ?: 'standard' }}">
                        <input type="hidden" name="billing_interval" id="billing_interval" value="{{ $billingCycle ?: 'termly' }}">
                        
                        <div class="text-right mt-4 pt-3 border-top border-secondary" style="border-color: rgba(255, 255, 255, 0.08) !important;">
                            <button type="button" class="btn btn-gradient-primary px-4 py-2 rounded-pill font-weight-semibold" onclick="nextStep(2)">
                                Next: Claim Subdomain <i class="material-symbols-rounded align-middle ml-1 font-size-sm">arrow_forward</i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- STEP 2: CHOOSE SUBDOMAIN --}}
                <div class="setup-step d-none" id="step-2">
                    <div class="glass-card p-4 p-md-5">
                        <div class="text-center mb-4">
                            <span class="badge badge-pill badge-info px-3 py-1 mb-2">STEP 2 OF 5</span>
                            <h3 class="text-white font-weight-bold">Claim Your Portal Web Address</h3>
                            <p class="text-muted mb-0">Choose your unique subdomain prefix for student, teacher, and admin access.</p>
                        </div>

                        <div class="form-group max-w-lg mx-auto mb-4">
                            <label class="text-white font-weight-semibold mb-2">Portal Subdomain</label>
                            <div class="input-group input-group-lg">
                                <input type="text" class="form-control text-right bg-dark text-white font-weight-bold" id="subdomain_input" name="subdomain" value="{{ $initialSubdomain ?? '' }}" required placeholder="e.g. greenwood" style="text-transform: lowercase; border-color: rgba(255, 255, 255, 0.15);">
                                <div class="input-group-append">
                                    <span class="input-group-text bg-secondary text-white font-weight-semibold">.{{ request()->getHost() }}</span>
                                </div>
                            </div>
                            <div class="mt-2" id="subdomain_feedback"></div>
                            <small class="text-muted d-block mt-2">Only letters, numbers, and hyphens allowed (e.g. <code>kingscollege</code> or <code>grace-high</code>).</small>
                        </div>

                        <div class="d-flex justify-content-between mt-5 pt-3 border-top border-secondary" style="border-color: rgba(255, 255, 255, 0.08) !important;">
                            <button type="button" class="btn btn-outline-glass px-4 py-2 rounded-pill" onclick="prevStep(1)">
                                <i class="material-symbols-rounded align-middle mr-1 font-size-sm">arrow_back</i> Back
                            </button>
                            <button type="button" class="btn btn-gradient-primary px-4 py-2 rounded-pill font-weight-semibold" id="btn-subdomain-next" onclick="validateSubdomainStep()" disabled>
                                Next: School Info <i class="material-symbols-rounded align-middle ml-1 font-size-sm">arrow_forward</i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- STEP 3: SCHOOL INFO --}}
                <div class="setup-step d-none" id="step-3">
                    <div class="glass-card p-4 p-md-5">
                        <div class="text-center mb-4">
                            <span class="badge badge-pill badge-info px-3 py-1 mb-2">STEP 3 OF 5</span>
                            <h3 class="text-white font-weight-bold">School Details &amp; Profile</h3>
                            <p class="text-muted mb-0">Enter the primary institutional credentials.</p>
                        </div>

                        <div class="max-w-lg mx-auto">
                            <div class="form-group mb-4">
                                <label class="text-white font-weight-semibold">Official School Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg bg-dark text-white" name="school_name" required placeholder="e.g. Greenwood International College">
                            </div>
                            <div class="form-group mb-4">
                                <label class="text-white font-weight-semibold">Official School Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control form-control-lg bg-dark text-white" name="school_email" required placeholder="e.g. info@greenwood.edu.ng">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-5 pt-3 border-top border-secondary" style="border-color: rgba(255, 255, 255, 0.08) !important;">
                            <button type="button" class="btn btn-outline-glass px-4 py-2 rounded-pill" onclick="prevStep(2)">
                                <i class="material-symbols-rounded align-middle mr-1 font-size-sm">arrow_back</i> Back
                            </button>
                            <button type="button" class="btn btn-gradient-primary px-4 py-2 rounded-pill font-weight-semibold" onclick="nextStep(4)">
                                Next: Admin Account <i class="material-symbols-rounded align-middle ml-1 font-size-sm">arrow_forward</i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- STEP 4: OWNER DETAILS --}}
                <div class="setup-step d-none" id="step-4">
                    <div class="glass-card p-4 p-md-5">
                        <div class="text-center mb-4">
                            <span class="badge badge-pill badge-info px-3 py-1 mb-2">STEP 4 OF 5</span>
                            <h3 class="text-white font-weight-bold">Create Super Administrator Account</h3>
                            <p class="text-muted mb-0">Set up master login credentials for your school management portal.</p>
                        </div>

                        <div class="max-w-lg mx-auto">
                            <div class="form-group mb-4">
                                <label class="text-white font-weight-semibold">Administrator Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg bg-dark text-white" name="owner_name" required placeholder="e.g. Dr. A. B. Ibrahim">
                            </div>
                            <div class="form-group mb-4">
                                <label class="text-white font-weight-semibold">Administrator Email (Login ID) <span class="text-danger">*</span></label>
                                <input type="email" class="form-control form-control-lg bg-dark text-white" name="owner_email" required placeholder="e.g. principal@greenwood.edu.ng">
                            </div>
                            <div class="form-group mb-4">
                                <label class="text-white font-weight-semibold">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control form-control-lg bg-dark text-white" name="owner_password" required placeholder="Minimum 8 characters">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-5 pt-3 border-top border-secondary" style="border-color: rgba(255, 255, 255, 0.08) !important;">
                            <button type="button" class="btn btn-outline-glass px-4 py-2 rounded-pill" onclick="prevStep(3)">
                                <i class="material-symbols-rounded align-middle mr-1 font-size-sm">arrow_back</i> Back
                            </button>
                            <button type="button" class="btn btn-gradient-primary px-4 py-2 rounded-pill font-weight-semibold" onclick="nextStep(5)">
                                Next: Payment Details <i class="material-symbols-rounded align-middle ml-1 font-size-sm">arrow_forward</i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- STEP 5: 1-TERM FREE TRIAL PAYMENT DETAILS & AUTHORIZATION --}}
                <div class="setup-step d-none" id="step-5">
                    <div class="glass-card p-4 p-md-5">
                        <div class="text-center mb-4">
                            <span class="badge badge-pill badge-info px-3 py-1 mb-2">STEP 5 OF 5 · 1-TERM FREE TRIAL</span>
                            <h3 class="text-white font-weight-bold">Payment Details &amp; Trial Authorization</h3>
                            <p class="text-muted mb-0">Provide card details to activate your 1-term trial. <strong>&#8358;0.00 charged today</strong>.</p>
                        </div>

                        <div class="max-w-lg mx-auto">
                            {{-- Order & Trial Summary Box --}}
                            <div class="p-3 rounded-3 mb-4" style="background: rgba(14, 165, 233, 0.08); border: 1px solid rgba(14, 165, 233, 0.25);">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom" style="border-color: rgba(255, 255, 255, 0.08) !important;">
                                    <span class="text-white font-weight-bold" id="summary-plan-name">Standard Plan</span>
                                    <span class="badge badge-info px-2 py-1 font-size-xs">1 Term Free (90 Days)</span>
                                </div>
                                <div class="d-flex justify-content-between text-muted font-size-sm mb-1">
                                    <span>Due Today:</span>
                                    <strong class="text-success font-size-base">&#8358;0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between text-muted font-size-sm mb-1">
                                    <span>Trial Expiration:</span>
                                    <span class="text-light">End of 1st Term (90 Days)</span>
                                </div>
                                <div class="d-flex justify-content-between text-muted font-size-sm">
                                    <span>Next Auto-Renewal:</span>
                                    <span class="text-white font-weight-bold" id="summary-renewal-price">&#8358;65,000 / term</span>
                                </div>
                            </div>

                            <input type="hidden" name="payment_method" value="trial_card">

                            {{-- Card Information Fields --}}
                            <div class="form-group mb-3">
                                <label class="text-light font-weight-semibold font-size-xs text-uppercase mb-1">Cardholder Full Name</label>
                                <input type="text" class="form-control bg-dark text-white font-size-sm" name="card_name" placeholder="e.g. DR. ABUBAKAR IBRAHIM" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="text-light font-weight-semibold font-size-xs text-uppercase mb-1">Card Number</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-dark border-secondary text-muted" style="border-color: rgba(255, 255, 255, 0.12) !important;">
                                            <i class="material-symbols-rounded font-size-sm">credit_card</i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control bg-dark text-white font-size-sm" id="card_number_input" name="card_number" placeholder="4242 &#8226;&#8226;&#8226;&#8226; &#8226;&#8226;&#8226;&#8226; 4242" maxlength="19" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6 form-group mb-0">
                                    <label class="text-light font-weight-semibold font-size-xs text-uppercase mb-1">Expiry (MM/YY)</label>
                                    <input type="text" class="form-control bg-dark text-white font-size-sm" name="card_expiry" placeholder="12/28" maxlength="5" required>
                                </div>
                                <div class="col-6 form-group mb-0">
                                    <label class="text-light font-weight-semibold font-size-xs text-uppercase mb-1">CVV / CVC</label>
                                    <input type="password" class="form-control bg-dark text-white font-size-sm" name="card_cvv" placeholder="123" maxlength="4" required>
                                </div>
                            </div>

                            {{-- Consent Checkbox --}}
                            <div class="custom-control custom-checkbox mb-4 mt-3">
                                <input type="checkbox" class="custom-control-input" id="autoDebitCheck" name="auto_debit_consent" required checked>
                                <label class="custom-control-label text-muted font-size-xs" for="autoDebitCheck">
                                    I agree to the 1-term trial terms. &#8358;0.00 is charged today. When the 90-day trial expires, my school portal will be automatically billed at the selected termly/yearly rate. I can cancel anytime with 1-click in the portal before the trial ends.
                                </label>
                            </div>

                            <div class="d-flex align-items-center justify-content-center text-muted font-size-xs mb-2">
                                <i class="material-symbols-rounded text-success mr-1 font-size-sm">lock</i>
                                <span>256-bit SSL Encrypted &amp; PCI-DSS Level 1 Compliant</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4 pt-3 border-top border-secondary" style="border-color: rgba(255, 255, 255, 0.08) !important;">
                            <button type="button" class="btn btn-outline-glass px-4 py-2 rounded-pill" onclick="prevStep(4)">
                                <i class="material-symbols-rounded align-middle mr-1 font-size-sm">arrow_back</i> Back
                            </button>
                            <button type="submit" class="btn btn-gradient-primary btn-lg px-5 py-3 rounded-pill font-weight-bold shadow-lg">
                                <i class="material-symbols-rounded align-middle mr-1">rocket_launch</i> Start 1-Term Free Trial (&#8358;0 Today)
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
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
    let wizardCycle = "{{ $billingCycle ?: 'termly' }}";

    $(document).ready(function() {
        // Step indicator click
        $('.step-indicator').on('click', function() {
            var step = parseInt($(this).data('step'));
            if (step > 2 && $('#btn-subdomain-next').attr('disabled')) {
                alert('Please enter and verify an available subdomain in Step 2 first.');
                return;
            }
            nextStep(step);
        });

        // Form submission
        $('#onboardForm').on('submit', function(e) {
            for (var i = 1; i <= 5; i++) {
                if (!validateStepFields(i)) {
                    e.preventDefault();
                    return false;
                }
            }
            var submitBtn = $(this).find('button[type="submit"]');
            submitBtn.attr('disabled', true).html('<i class="material-symbols-rounded spinner align-middle mr-1">sync</i> Activating 1-Term Trial &amp; Provisioning...');
            return true;
        });

        // Subdomain input live check
        $('#subdomain_input').on('input', function() {
            var val = $(this).val().toLowerCase().replace(/[^a-z0-9\-]/g, '');
            $(this).val(val);
            
            if (val.length < 3) {
                $('#subdomain_feedback').html('<small class="text-warning">Minimum 3 characters required.</small>');
                $('#btn-subdomain-next').attr('disabled', true);
                return;
            }

            $('#subdomain_feedback').html('<small class="text-muted"><i class="material-symbols-rounded font-size-xs align-middle">sync</i> Checking availability...</small>');
            
            $.get("{{ route('onboard.check_subdomain') }}", { subdomain: val }, function(res) {
                if (res.available) {
                    $('#subdomain_feedback').html('<small class="text-success"><i class="material-symbols-rounded font-size-xs align-middle">check_circle</i> <strong>' + val + '.{{ request()->getHost() }}</strong> is available!</small>');
                    $('#btn-subdomain-next').removeAttr('disabled');
                } else {
                    $('#subdomain_feedback').html('<small class="text-danger"><i class="material-symbols-rounded font-size-xs align-middle">cancel</i> ' + (res.message || 'Subdomain already taken. Try another.') + '</small>');
                    $('#btn-subdomain-next').attr('disabled', true);
                }
            });
        });

        // Auto format card number input
        $('#card_number_input').on('input', function() {
            let val = $(this).val().replace(/\D/g, '');
            let newVal = '';
            for(let i = 0; i < val.length; i++) {
                if(i > 0 && i % 4 === 0) newVal += ' ';
                newVal += val[i];
            }
            $(this).val(newVal);
        });

        // Trigger live lookup if pre-filled
        if ($('#subdomain_input').val().trim().length > 0) {
            $('#subdomain_input').trigger('input');
        }

        updateSummaryBox();
    });

    function setWizardCycle(cycle) {
        wizardCycle = cycle;
        $('#billing_interval').val(cycle);
        if (cycle === 'termly') {
            $('#btn-wiz-termly').addClass('active-toggle-btn text-white').removeClass('text-muted');
            $('#btn-wiz-yearly').removeClass('active-toggle-btn text-white').addClass('text-muted');
            $('.wiz-price-termly').removeClass('d-none');
            $('.wiz-price-yearly').addClass('d-none');
        } else {
            $('#btn-wiz-yearly').addClass('active-toggle-btn text-white').removeClass('text-muted');
            $('#btn-wiz-termly').removeClass('active-toggle-btn text-white').addClass('text-muted');
            $('.wiz-price-yearly').removeClass('d-none');
            $('.wiz-price-termly').addClass('d-none');
        }
        updateSummaryBox();
    }

    function selectWizardPlan(slug, element) {
        $('.plan-selector-card').removeClass('popular border-info');
        $(element).addClass('popular border-info');
        $('#plan_slug').val(slug);
        updateSummaryBox();
    }

    function updateSummaryBox() {
        const activeCard = $('.plan-selector-card.popular');
        if (activeCard.length) {
            const planName = activeCard.data('name');
            const termlyPrice = activeCard.data('termly');
            const yearlyPrice = activeCard.data('yearly');
            
            $('#summary-plan-name').text(planName + ' (' + (wizardCycle === 'yearly' ? 'Yearly' : 'Termly') + ')');
            if (wizardCycle === 'yearly') {
                $('#summary-renewal-price').text('₦' + Number(yearlyPrice).toLocaleString() + ' / year');
            } else {
                $('#summary-renewal-price').text('₦' + Number(termlyPrice).toLocaleString() + ' / term');
            }
        }
    }

    function validateStepFields(stepNum) {
        if (stepNum === 1) return true;
        if (stepNum === 2) {
            if ($('#btn-subdomain-next').attr('disabled')) {
                alert('Please enter and verify an available subdomain in Step 2 first.');
                return false;
            }
            return true;
        }
        
        var isValid = true;
        $('#step-' + stepNum + ' [required]').each(function() {
            if (!this.checkValidity()) {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        return isValid;
    }

    function nextStep(target) {
        var current = target - 1;
        if (!validateStepFields(current)) {
            return;
        }

        $('.setup-step').addClass('d-none');
        $('#step-' + target).removeClass('d-none');

        $('.step-indicator').removeClass('active').addClass('text-muted');
        $('.step-indicator[data-step="' + target + '"]').addClass('active').removeClass('text-muted');
        
        window.scrollTo({ top: 50, behavior: 'smooth' });
    }

    function prevStep(target) {
        $('.setup-step').addClass('d-none');
        $('#step-' + target).removeClass('d-none');

        $('.step-indicator').removeClass('active').addClass('text-muted');
        $('.step-indicator[data-step="' + target + '"]').addClass('active').removeClass('text-muted');
        
        window.scrollTo({ top: 50, behavior: 'smooth' });
    }

    function validateSubdomainStep() {
        if ($('#subdomain_input').val().length < 3) {
            alert('Please provide a valid subdomain prefix.');
            return;
        }
        nextStep(3);
    }
</script>
@endsection
