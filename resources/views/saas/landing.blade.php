@extends('layouts.saas_master')

@section('page_title', 'All-in-One Cloud School Management Portal')

@section('content')

{{-- HERO SECTION --}}
<div class="container hero-section py-5">
    <div class="row align-items-center">
        <div class="col-lg-7 pr-lg-5">
            <div class="d-inline-flex align-items-center px-3 py-1 rounded-pill mb-3" style="background: rgba(14, 165, 233, 0.12); border: 1px solid rgba(14, 165, 233, 0.35);">
                <span class="badge badge-info badge-pill mr-2">ENTERPRISE</span>
                <span class="text-info font-weight-bold font-size-sm">{{ $cms['hero_badge'] ?? 'Next-Gen Multi-Tenant School ERP' }}</span>
            </div>

            <h1 class="display-4 font-weight-bold text-white mb-4" style="line-height: 1.15; letter-spacing: -1.5px;">
                {{ $cms['hero_title'] ?? 'The Complete Operating System for Modern Schools' }}
            </h1>

            <p class="lead text-muted mb-4 font-size-lg" style="line-height: 1.65;">
                {{ $cms['hero_subtitle'] ?? 'From continuous assessments (CA 1, CA 2, Exams) and automated WAEC grading to bursary fee lockdown, daily attendance registers, and parent SMS broadcasts — all in one blazing-fast, dedicated cloud portal.' }}
            </p>

            {{-- Live Subdomain Instant Claimer Bar --}}
            <div class="glass-card p-2 mb-4 shadow-lg" style="max-width: 540px; border: 1px solid rgba(56, 189, 248, 0.3);">
                <form action="{{ route('onboard.wizard') }}" method="GET" class="d-flex align-items-center">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-transparent border-0 text-muted font-size-sm pl-2">https://</span>
                        </div>
                        <input type="text" name="subdomain" id="hero_subdomain" class="form-control bg-transparent border-0 text-white font-weight-bold" placeholder="your-school-name" style="box-shadow: none;" required>
                        <div class="input-group-append">
                            <span class="input-group-text bg-transparent border-0 text-muted font-size-sm pr-2">.{{ request()->getHost() }}</span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-gradient-primary px-3 py-2 rounded-pill font-weight-semibold flex-shrink-0">
                        Claim Portal <i class="material-symbols-rounded align-middle font-size-xs ml-1">arrow_forward</i>
                    </button>
                </form>
            </div>

            <div class="d-flex flex-wrap gap-3 mb-4">
                <a href="{{ route('onboard.wizard') }}" class="btn btn-gradient-primary btn-lg px-4 py-3 rounded-pill mr-3 mb-2 shadow-lg font-weight-bold">
                    <i class="material-symbols-rounded align-middle mr-1">rocket_launch</i> {{ $cms['hero_cta_text'] ?? 'Start 1-Term Free Trial' }}
                </a>
                <a href="{{ route('saas.demo') }}" class="btn btn-outline-glass btn-lg px-4 py-3 rounded-pill mb-2 font-weight-semibold">
                    <i class="material-symbols-rounded align-middle mr-1">play_circle</i> {{ $cms['demo_cta_text'] ?? 'Book Live Demo' }}
                </a>
            </div>

            <div class="row pt-2 text-muted small">
                <div class="col-auto d-flex align-items-center mb-2">
                    <i class="material-symbols-rounded text-success mr-1" style="font-size: 18px;">check_circle</i> {{ $cms['hero_cta_sub'] ?? '1-Term Free · ₦0 Today' }}
                </div>
                <div class="col-auto d-flex align-items-center mb-2">
                    <i class="material-symbols-rounded text-success mr-1" style="font-size: 18px;">check_circle</i> 2-Minute Instant Setup
                </div>
                <div class="col-auto d-flex align-items-center mb-2">
                    <i class="material-symbols-rounded text-success mr-1" style="font-size: 18px;">check_circle</i> 100% Isolated Database
                </div>
            </div>
        </div>

        {{-- Hero Visual Simulation Mockup Card --}}
        <div class="col-lg-5 mt-5 mt-lg-0">
            <div class="glass-card p-4 shadow-2xl position-relative border-glow">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom" style="border-color: rgba(255, 255, 255, 0.08) !important;">
                    <div class="d-flex align-items-center">
                        <span class="status-dot mr-2 bg-success"></span>
                        <strong class="text-white font-size-sm">Live System Simulator</strong>
                    </div>
                    <span class="badge badge-outline-info font-size-xs px-2 py-1">v2.4 Ready</span>
                </div>

                {{-- Interactive Tabs for Live Simulation --}}
                <div class="btn-group btn-group-sm w-100 mb-3" role="group">
                    <button type="button" class="btn btn-outline-glass active-sim-tab text-white" id="sim-tab-ca" onclick="switchSimTab('ca')">Continuous Assessment</button>
                    <button type="button" class="btn btn-outline-glass text-muted" id="sim-tab-bursary" onclick="switchSimTab('bursary')">Bursary Lock</button>
                    <button type="button" class="btn btn-outline-glass text-muted" id="sim-tab-report" onclick="switchSimTab('report')">Report Card</button>
                </div>

                {{-- Simulation View 1: Continuous Assessment --}}
                <div id="sim-view-ca" class="sim-view">
                    <div class="d-flex justify-content-between align-items-center mb-2 text-muted font-size-xs">
                        <span>Class: <strong>JSS 1 Gold</strong></span>
                        <span>Subject: <strong>Mathematics</strong></span>
                    </div>
                    <div class="p-2 rounded mb-2" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.06);">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-white small font-weight-bold">Chinedu Eze (ADM-042)</span>
                            <span class="badge badge-success">Grade A (84%)</span>
                        </div>
                        <div class="row text-center font-size-xs text-muted mt-2">
                            <div class="col-4">CA 1 (20): <strong class="text-info">18</strong></div>
                            <div class="col-4">CA 2 (20): <strong class="text-info">19</strong></div>
                            <div class="col-4">Exam (60): <strong class="text-info">47</strong></div>
                        </div>
                    </div>
                    <div class="p-2 rounded" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.06);">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-white small font-weight-bold">Fatima Mohammed (ADM-088)</span>
                            <span class="badge badge-primary">Grade B (68%)</span>
                        </div>
                        <div class="row text-center font-size-xs text-muted mt-2">
                            <div class="col-4">CA 1 (20): <strong class="text-info">14</strong></div>
                            <div class="col-4">CA 2 (20): <strong class="text-info">15</strong></div>
                            <div class="col-4">Exam (60): <strong class="text-info">39</strong></div>
                        </div>
                    </div>
                </div>

                {{-- Simulation View 2: Bursary Lock --}}
                <div id="sim-view-bursary" class="sim-view p-2 d-none">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Termly Fee Invoicing</span>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="feeStatusToggle" onchange="toggleFeeStatus(this)">
                            <label class="custom-control-label text-white small" for="feeStatusToggle">Simulate Default</label>
                        </div>
                    </div>
                    <div class="p-3 rounded mb-2" id="fee-status-card" style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3);">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted font-size-xs d-block">Student: Ibrahim Musa</span>
                                <strong class="text-white font-size-sm" id="fee-status-text">100% Settled (&#8358;75,000 / &#8358;75,000)</strong>
                            </div>
                            <span class="badge badge-success px-2 py-1" id="fee-status-badge">Unlocked ✅</span>
                        </div>
                        <div class="text-muted font-size-xs mt-2" id="fee-status-desc">
                            Report card is fully accessible and printable by student &amp; parent.
                        </div>
                    </div>
                </div>

                {{-- Simulation View 3: Report Card --}}
                <div id="sim-view-report" class="sim-view p-2 d-none">
                    <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08);">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-white font-weight-bold small">Official Term Report Card</span>
                            <span class="badge badge-success">Rank: 1st / 122</span>
                        </div>
                        <div class="row text-center font-size-xs text-muted mb-2">
                            <div class="col-4">Subjects: <strong class="text-white">13</strong></div>
                            <div class="col-4">Average: <strong class="text-info">88.4%</strong></div>
                            <div class="col-4">GPA: <strong class="text-success">4.85</strong></div>
                        </div>
                        <div class="font-size-xs text-muted border-top pt-2" style="border-color: rgba(255, 255, 255, 0.08) !important;">
                            <em>"Exemplary academic dedication and outstanding character."</em> — Principal
                        </div>
                    </div>
                </div>

                {{-- Mock Live Stats Footer --}}
                <div class="row text-center mt-3 pt-2 border-top" style="border-color: rgba(255, 255, 255, 0.08) !important;">
                    <div class="col-4">
                        <span class="h6 text-info font-weight-bold mb-0 d-block">{{ $cms['stat_students'] ?? '45,000+' }}</span>
                        <small class="text-muted font-size-xs">Enrolled</small>
                    </div>
                    <div class="col-4">
                        <span class="h6 text-success font-weight-bold mb-0 d-block">{{ $cms['stat_uptime'] ?? '99.9%' }}</span>
                        <small class="text-muted font-size-xs">Uptime SLA</small>
                    </div>
                    <div class="col-4">
                        <span class="h6 text-purple font-weight-bold mb-0 d-block">{{ $cms['stat_schools'] ?? '150+' }}</span>
                        <small class="text-muted font-size-xs">Schools</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TRUST & CURRICULUM BADGES --}}
<div class="container my-4 text-center">
    <p class="text-muted font-size-xs text-uppercase font-weight-bold tracking-widest mb-3" style="letter-spacing: 2px;">
        Designed for Nigerian &amp; International Standards
    </p>
    <div class="d-flex justify-content-center align-items-center flex-wrap gap-3 opacity-75">
        <div class="badge badge-dark px-3 py-2 mr-2 mb-2 border border-secondary">WAEC / NECO Secondary Scale</div>
        <div class="badge badge-dark px-3 py-2 mr-2 mb-2 border border-secondary">Universal Basic Education (UBE)</div>
        <div class="badge badge-dark px-3 py-2 mr-2 mb-2 border border-secondary">British &amp; Cambridge Curriculum</div>
        <div class="badge badge-dark px-3 py-2 mr-2 mb-2 border border-secondary">Paystack &amp; Flutterwave Ready</div>
    </div>
</div>

{{-- CORE MODULE PILLARS --}}
<div class="container my-5 py-5">
    <div class="text-center mb-5">
        <span class="badge badge-pill badge-info px-3 py-1 mb-2 font-weight-bold">FULL CAPABILITY SUITE</span>
        <h2 class="text-white font-weight-bold display-5 mb-3">Engineered for Every School Role</h2>
        <p class="text-muted font-size-lg max-w-2xl mx-auto">Eliminate messy spreadsheets and disjointed software with purpose-built workspaces.</p>
    </div>

    <div class="row">
        @foreach ($cms['features'] ?? [] as $feat)
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="feature-card h-100">
                    <div class="rounded-circle d-inline-flex p-3 mb-4" style="background: rgba(14, 165, 233, 0.15);">
                        <i class="material-symbols-rounded text-{{ $feat['color'] ?? 'info' }}" style="font-size: 32px;">{{ $feat['icon'] ?? 'star' }}</i>
                    </div>
                    <h5 class="text-white font-weight-bold mb-2">{{ $feat['title'] }}</h5>
                    <p class="text-muted font-size-sm mb-0">
                        {{ $feat['desc'] }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- ARCHITECTURE HIGHLIGHT: 100% TENANT ISOLATION --}}
<div class="container my-5 py-5">
    <div class="glass-card p-5 border-glow">
        <div class="row align-items-center">
            <div class="col-lg-6 pr-lg-5">
                <div class="badge badge-success px-3 py-1 rounded-pill mb-3 font-weight-bold">
                    <i class="material-symbols-rounded align-middle mr-1 font-size-xs">shield</i> ZERO DATA LEAKAGE
                </div>
                <h3 class="text-white font-weight-bold display-6 mb-3">
                    Every School Gets a <span class="brand-gradient">Dedicated Database</span>
                </h3>
                <p class="text-muted mb-4 font-size-md" style="line-height: 1.7;">
                    Unlike legacy school software that mashes multiple schools into a single shared database, Digiskul provisions a completely isolated database schema and custom subdomain for every institution.
                </p>
                <ul class="list-unstyled text-muted font-size-sm mb-4">
                    <li class="d-flex align-items-center mb-2">
                        <i class="material-symbols-rounded text-success mr-2">verified</i> 256-Bit SSL Enterprise Grade Data Encryption
                    </li>
                    <li class="d-flex align-items-center mb-2">
                        <i class="material-symbols-rounded text-success mr-2">verified</i> Automated Nightly Cloud Database Backups
                    </li>
                    <li class="d-flex align-items-center mb-2">
                        <i class="material-symbols-rounded text-success mr-2">verified</i> Two-Factor Authentication (2FA) for All Administrators
                    </li>
                </ul>
            </div>
            <div class="col-lg-6">
                <div class="p-4 rounded-3 text-center" style="background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(255, 255, 255, 0.08);">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="glass-card p-3">
                                <i class="material-symbols-rounded text-info mb-2 font-size-xl">domain</i>
                                <h6 class="text-white font-weight-bold mb-1">Custom Subdomain</h6>
                                <small class="text-muted"><code>yourname.digiskul.app</code></small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="glass-card p-3">
                                <i class="material-symbols-rounded text-success mb-2 font-size-xl">database</i>
                                <h6 class="text-white font-weight-bold mb-1">Dedicated DB</h6>
                                <small class="text-muted">Isolated MySQL Schema</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="glass-card p-3">
                                <i class="material-symbols-rounded text-warning mb-2 font-size-xl">admin_panel_settings</i>
                                <h6 class="text-white font-weight-bold mb-1">Role Scoping</h6>
                                <small class="text-muted">Strict Class &amp; Subject RBAC</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="glass-card p-3">
                                <i class="material-symbols-rounded text-purple mb-2 font-size-xl">bolt</i>
                                <h6 class="text-white font-weight-bold mb-1">Sub-Second</h6>
                                <small class="text-muted">Blazing Fast Query Speed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- COMPARISON MATRIX --}}
@if(!empty($cms['comparison']))
<div class="container my-5 py-5">
    <div class="text-center mb-5">
        <span class="badge badge-pill badge-info px-3 py-1 mb-2 font-weight-bold">WHY CHOOSE US</span>
        <h2 class="text-white font-weight-bold display-5 mb-3">Traditional School Software vs. Digiskul</h2>
        <p class="text-muted font-size-lg">See why forward-thinking institutions are modernizing with our cloud ERP.</p>
    </div>

    <div class="glass-card p-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-borderless text-light mb-0 align-middle">
                <thead>
                    <tr class="border-bottom" style="border-color: rgba(255, 255, 255, 0.1) !important;">
                        <th class="text-muted font-size-sm py-3" style="width: 25%;">Feature Capability</th>
                        <th class="text-muted font-size-sm py-3" style="width: 35%;">Traditional School Portals</th>
                        <th class="text-info font-weight-bold font-size-sm py-3" style="width: 40%;">Digiskul Cloud ERP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cms['comparison'] as $comp)
                        <tr class="border-bottom" style="border-color: rgba(255, 255, 255, 0.05) !important;">
                            <td class="font-weight-bold text-white py-3">{{ $comp['feature'] }}</td>
                            <td class="text-muted py-3">
                                <i class="material-symbols-rounded text-danger mr-1 align-middle font-size-sm">close</i>
                                {{ $comp['traditional'] }}
                            </td>
                            <td class="text-light py-3">
                                <i class="material-symbols-rounded text-success mr-1 align-middle font-size-sm">check_circle</i>
                                <strong>{{ $comp['digiskul'] }}</strong>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- TESTIMONIALS SECTION --}}
@if(!empty($cms['testimonials']))
<div class="container my-5 py-5">
    <div class="text-center mb-5">
        <span class="badge badge-pill badge-info px-3 py-1 mb-2 font-weight-bold">TESTIMONIALS</span>
        <h2 class="text-white font-weight-bold display-5 mb-3">Trusted by Leading Educators</h2>
        <p class="text-muted font-size-lg">Hear directly from school proprietors, principals, and directors.</p>
    </div>

    <div class="row">
        @foreach ($cms['testimonials'] as $t)
            <div class="col-md-4 mb-4">
                <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="text-warning mb-3">
                            @for ($s = 0; $s < ($t['rating'] ?? 5); $s++)
                                <i class="material-symbols-rounded align-middle font-size-sm">star</i>
                            @endfor
                        </div>
                        <p class="text-light font-size-sm mb-4" style="line-height: 1.7; font-style: italic;">
                            "{{ $t['quote'] }}"
                        </p>
                    </div>
                    <div class="d-flex align-items-center pt-3 border-top" style="border-color: rgba(255, 255, 255, 0.08) !important;">
                        <div class="rounded-circle d-inline-flex p-2 mr-3 bg-info text-white font-weight-bold">
                            <i class="material-symbols-rounded">person</i>
                        </div>
                        <div>
                            <strong class="text-white d-block font-size-sm">{{ $t['name'] }}</strong>
                            <small class="text-muted">{{ $t['role'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- SUBSCRIPTION PLANS SECTION --}}
<div class="container my-5 py-5" id="pricing">
    <div class="text-center mb-5">
        <span class="badge badge-pill badge-info px-3 py-1 mb-2 font-weight-bold">TRANSPARENT SUBSCRIPTION</span>
        <h2 class="text-white font-weight-bold display-5">Predictable Pricing for Growing Schools</h2>
        <p class="text-muted font-size-lg">Choose your package. All plans include a 1-term free trial (90 days) with &#8358;0 charged today.</p>
    </div>

    <div class="row justify-content-center">
        @foreach ($plans as $plan)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="plan-card h-100 p-4 d-flex flex-column justify-content-between {{ $plan->slug === 'standard' ? 'popular' : '' }}">
                    @if ($plan->slug === 'standard')
                        <div class="position-absolute" style="top: -12px; right: 24px;">
                            <span class="badge badge-info px-3 py-1 shadow font-weight-bold" style="letter-spacing: 0.5px;">RECOMMENDED</span>
                        </div>
                    @endif

                    <div>
                        <h4 class="text-white font-weight-bold mb-2">{{ $plan->name }}</h4>
                        <p class="text-muted font-size-sm mb-4">Ideal for expanding schools requiring full academic &amp; financial automation.</p>

                        <div class="mb-4 pb-3 border-bottom border-secondary" style="border-color: rgba(255, 255, 255, 0.08) !important;">
                            <span class="h1 text-white font-weight-bold">&#8358;{{ number_format($plan->termly_price ?? ($plan->monthly_price * 3.5)) }}</span>
                            <span class="text-muted font-size-sm">/ term</span>
                            <div class="text-muted font-size-xs mt-1">or &#8358;{{ number_format($plan->yearly_price) }} / year (Save 15%)</div>
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
                                90-Day 1-Term Free Trial Included
                            </li>
                            <li class="d-flex align-items-center mb-3 text-light font-size-sm">
                                <i class="material-symbols-rounded text-success mr-2 font-size-sm">check</i>
                                Complete Academic Assessments &amp; Moderation
                            </li>
                            <li class="d-flex align-items-center mb-3 text-light font-size-sm">
                                <i class="material-symbols-rounded text-success mr-2 font-size-sm">check</i>
                                Printable Termly Report Cards &amp; Rankings
                            </li>
                            <li class="d-flex align-items-center mb-3 text-light font-size-sm">
                                <i class="material-symbols-rounded text-success mr-2 font-size-sm">check</i>
                                Daily Attendance Register &amp; Stats
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
                        </ul>
                    </div>

                    <a href="{{ route('onboard.wizard', ['plan' => $plan->slug]) }}" class="btn {{ $plan->slug === 'standard' ? 'btn-gradient-primary' : 'btn-outline-glass' }} btn-block py-2 rounded-pill font-weight-semibold">
                        Choose {{ $plan->name }} <i class="material-symbols-rounded align-middle ml-1 font-size-sm">arrow_forward</i>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- FREQUENTLY ASKED QUESTIONS --}}
@if(!empty($cms['faqs']))
<div class="container my-5 py-5">
    <div class="text-center mb-5">
        <h2 class="text-white font-weight-bold display-5 mb-3">Frequently Asked Questions</h2>
        <p class="text-muted font-size-lg">Everything you need to know about setting up your school portal.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="accordion" id="faqAccordion">
                @foreach ($cms['faqs'] as $fIdx => $faq)
                    <div class="glass-card mb-3 p-3">
                        <h6 class="mb-0 text-white font-weight-bold cursor-pointer" data-toggle="collapse" data-target="#faq{{ $fIdx }}">
                            <i class="material-symbols-rounded text-info align-middle mr-2">help</i> {{ $faq['q'] }}
                        </h6>
                        <div id="faq{{ $fIdx }}" class="collapse {{ $fIdx === 0 ? 'show' : '' }} mt-2 text-muted font-size-sm">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

{{-- BOTTOM CTA BANNER --}}
<div class="container my-5 pb-5">
    <div class="p-5 rounded-3 text-center position-relative overflow-hidden" style="background: linear-gradient(135deg, rgba(14, 165, 233, 0.2) 0%, rgba(99, 102, 241, 0.2) 100%); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 24px;">
        <h2 class="text-white font-weight-bold display-5 mb-3">Ready to Modernize Your School Operations?</h2>
        <p class="text-muted font-size-lg max-w-xl mx-auto mb-4">
            Join forward-thinking educators and administrators. Activate your 1-term free trial (90 days) in 2 minutes.
        </p>
        <a href="{{ route('onboard.wizard') }}" class="btn btn-gradient-primary btn-lg px-5 py-3 rounded-pill shadow-lg font-weight-bold">
            <i class="material-symbols-rounded align-middle mr-1">rocket_launch</i> Start 1-Term Free Trial
        </a>
    </div>
</div>

<script>
    function switchSimTab(tab) {
        $('.sim-view').addClass('d-none');
        $('#sim-tab-ca, #sim-tab-bursary, #sim-tab-report').removeClass('active-sim-tab text-white').addClass('text-muted');
        
        $('#sim-tab-' + tab).addClass('active-sim-tab text-white').removeClass('text-muted');
        $('#sim-view-' + tab).removeClass('d-none');
    }

    function toggleFeeStatus(elem) {
        if (elem.checked) {
            $('#fee-status-card').css({
                'background': 'rgba(239, 68, 68, 0.1)',
                'border-color': 'rgba(239, 68, 68, 0.3)'
            });
            $('#fee-status-text').html('Defaulter (<span class="text-danger">&#8358;35,000 Unpaid</span>)');
            $('#fee-status-badge').removeClass('badge-success').addClass('badge-danger').text('Locked 🔒');
            $('#fee-status-desc').html('<span class="text-danger">⚠️ Report card locked!</span> Settlement required before student or parent can view term results.');
        } else {
            $('#fee-status-card').css({
                'background': 'rgba(34, 197, 94, 0.1)',
                'border-color': 'rgba(34, 197, 94, 0.3)'
            });
            $('#fee-status-text').html('100% Settled (&#8358;75,000 / &#8358;75,000)');
            $('#fee-status-badge').removeClass('badge-danger').addClass('badge-success').text('Unlocked ✅');
            $('#fee-status-desc').text('Report card is fully accessible and printable by student & parent.');
        }
    }
</script>
@endsection
