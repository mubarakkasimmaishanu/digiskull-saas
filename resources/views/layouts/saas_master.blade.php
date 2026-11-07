<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $settings = Qs::getSettings();
        $sysName = $settings->where('type', 'system_name')->value('description') ?: 'Digiskul';
        if (str_contains(strtolower($sysName), 'manangement')) {
            $sysName = 'Digiskul';
        }
    @endphp

    <title>{{ $sysName }} &#183; @yield('page_title')</title>

    @include('partials.fav_icons')

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
    
    {{-- Theme CSS --}}
    <link href="{{ asset('assets/css/bootstrap-min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/bootstrap_limitless-min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/components-min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/colors-min.css') }}" rel="stylesheet" type="text/css">

    <!-- Core JS files -->
    <script src="{{ asset('global_assets/js/main/jquery.min.js') }}"></script>
    <script src="{{ asset('global_assets/js/main/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/app-min.js') }}"></script>

    <style>
        :root {
            --primary-blue: #2563eb;
            --primary-blue-hover: #1d4ed8;
            --card-bg: rgba(30, 41, 59, 0.5);
            --card-border: rgba(255, 255, 255, 0.08);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
        }

        html, body {
            color-scheme: dark;
            background-color: #0b0f19 !important;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #cbd5e1;
            overflow-x: hidden;
        }

        .navbar-saas {
            background-color: rgba(11, 15, 25, 0.95) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .brand-gradient {
            color: #ffffff;
            font-weight: 700;
        }

        .btn-gradient-primary {
            background: #2563eb;
            color: #ffffff !important;
            border: 1px solid #3b82f6;
            font-weight: 600;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease;
        }

        .btn-gradient-primary:hover {
            background: #1d4ed8;
            border-color: #2563eb;
            color: #ffffff !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }

        .btn-outline-glass {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #ffffff !important;
            font-weight: 600;
            backdrop-filter: blur(8px);
            transition: all 0.2s ease;
        }

        .btn-outline-glass:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
            color: #ffffff !important;
            transform: translateY(-1px);
        }

        .nav-link-saas {
            color: #94a3b8 !important;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 0.5rem 1rem !important;
            transition: color 0.2s ease;
        }

        .nav-link-saas:hover, .nav-link-saas.active {
            color: #ffffff !important;
        }

        .glass-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .feature-card {
            background: rgba(30, 41, 59, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            padding: 2rem;
            transition: all 0.25s ease;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            border-color: rgba(255, 255, 255, 0.15);
            box-shadow: 0 12px 24px -10px rgba(0, 0, 0, 0.4);
            background: rgba(30, 41, 59, 0.7);
        }

        .plan-card {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            transition: all 0.25s ease;
            position: relative;
        }

        .plan-card:hover {
            transform: translateY(-4px);
            border-color: rgba(255, 255, 255, 0.18);
            box-shadow: 0 16px 32px -12px rgba(0, 0, 0, 0.5);
        }

        .plan-card.popular {
            border: 1px solid #3b82f6;
            background: rgba(30, 41, 59, 0.8);
            box-shadow: 0 8px 24px -6px rgba(0, 0, 0, 0.5);
        }

        .footer-saas {
            background-color: #080c14;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            padding: 60px 0 30px;
            margin-top: 100px;
            position: relative;
            z-index: 1;
        }
    </style>
</head>

<body>
    <!-- SaaS Sticky Navbar -->
    <nav class="navbar navbar-expand-lg navbar-saas sticky-top py-3">
        <div class="container">
            <a href="{{ route('saas.landing') }}" class="navbar-brand d-flex align-items-center">
                <img src="{{ asset('global_assets/images/digiskul_logo_mark.svg') }}" alt="Digiskul Logo" width="34" height="34" class="mr-2">
                <span class="h4 font-weight-bold mb-0 text-white tracking-tight">
                    Digiskul
                </span>
            </a>

            <button class="navbar-toggler border-0 text-white" type="button" data-toggle="collapse" data-target="#saasNavbar">
                <span class="material-symbols-rounded">menu</span>
            </button>

            <div class="collapse navbar-collapse" id="saasNavbar">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a href="{{ route('saas.landing') }}" class="nav-link nav-link-saas {{ request()->routeIs('saas.landing') ? 'active' : '' }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('saas.features') }}" class="nav-link nav-link-saas {{ request()->routeIs('saas.features') ? 'active' : '' }}">Features</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('saas.pricing') }}" class="nav-link nav-link-saas {{ request()->routeIs('saas.pricing') ? 'active' : '' }}">Pricing</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('saas.contact') }}" class="nav-link nav-link-saas {{ request()->routeIs('saas.contact') ? 'active' : '' }}">Contact</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center mt-3 mt-lg-0">
                    <a href="{{ route('login') }}" class="btn btn-link text-light mr-2 font-weight-semibold">Sign In</a>
                    <a href="{{ route('onboard.wizard') }}" class="btn btn-gradient-primary px-4 py-2 rounded-pill">
                        Start Free Trial <span class="material-symbols-rounded align-middle ml-1" style="font-size: 18px;">arrow_forward</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main style="position: relative; z-index: 1;">
        @if (session('pop_success'))
            <div class="container mt-4">
                <div class="alert alert-success alert-styled-left alert-dismissible bg-success-alpha text-white border-0">
                    <button type="button" class="close text-white" data-dismiss="alert"><span>&times;</span></button>
                    <span><i class="material-symbols-rounded mr-1 align-middle">check_circle</i> {{ session('pop_success') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="container mt-4">
                <div class="alert alert-danger alert-styled-left alert-dismissible bg-danger-alpha text-white border-0">
                    <button type="button" class="close text-white" data-dismiss="alert"><span>&times;</span></button>
                    <span>{!! implode('<br>', $errors->all()) !!}</span>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- SaaS Footer -->
    <footer class="footer-saas">
        <div class="container">
            <div class="row">
                <div class="col-lg-5 mb-4 mb-lg-0">
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ asset('global_assets/images/digiskul_logo_mark.svg') }}" alt="Digiskul Logo" width="30" height="30" class="mr-2">
                        <h4 class="font-weight-bold mb-0 text-white">Digiskul</h4>
                    </div>
                    <p class="text-muted pr-lg-5">
                        The ultimate cloud school management ERP designed for primary, secondary, and private schools. Complete multi-tenant isolation, automated continuous assessments, bursary fee lockdown, and teacher management.
                    </p>
                    <div class="d-flex gap-3 text-muted small mt-3">
                        <span class="mr-3"><i class="material-symbols-rounded text-success align-middle mr-1" style="font-size: 16px;">lock</i> 256-bit SSL</span>
                        <span class="mr-3"><i class="material-symbols-rounded text-info align-middle mr-1" style="font-size: 16px;">database</i> Isolated Databases</span>
                        <span><i class="material-symbols-rounded text-warning align-middle mr-1" style="font-size: 16px;">verified_user</i> 99.9% SLA</span>
                    </div>
                </div>
                <div class="col-lg-2 col-6 mb-4 mb-lg-0">
                    <h6 class="text-white font-weight-bold mb-3">Platform</h6>
                    <ul class="list-unstyled text-muted font-size-sm">
                        <li class="mb-2"><a href="{{ route('saas.features') }}" class="text-muted hover-light">Features</a></li>
                        <li class="mb-2"><a href="{{ route('saas.pricing') }}" class="text-muted hover-light">Pricing Plans</a></li>
                        <li class="mb-2"><a href="{{ route('onboard.wizard') }}" class="text-muted hover-light">1-Term Free Trial</a></li>
                        <li class="mb-2"><a href="{{ route('saas.demo') }}" class="text-muted hover-light">Request Demo</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-6 mb-4 mb-lg-0">
                    <h6 class="text-white font-weight-bold mb-3">Core Modules</h6>
                    <ul class="list-unstyled text-muted font-size-sm">
                        <li class="mb-2"><span class="text-muted">Academic Assessments</span></li>
                        <li class="mb-2"><span class="text-muted">Bursary &amp; School Fees</span></li>
                        <li class="mb-2"><span class="text-muted">Teacher Workspaces</span></li>
                        <li class="mb-2"><span class="text-muted">Report Card Lockdown</span></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-12">
                    <h6 class="text-white font-weight-bold mb-3">Support &amp; Security</h6>
                    <ul class="list-unstyled text-muted font-size-sm">
                        <li class="mb-2"><a href="{{ route('saas.contact') }}" class="text-muted hover-light">Help &amp; Contact</a></li>
                        <li class="mb-2"><a href="{{ route('privacy_policy') }}" class="text-muted hover-light">Privacy Policy</a></li>
                        <li class="mb-2"><a href="{{ route('terms_of_use') }}" class="text-muted hover-light">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <hr class="border-secondary my-4" style="border-color: rgba(255, 255, 255, 0.08) !important;">
            <div class="d-flex justify-content-between align-items-center flex-wrap text-muted small">
                <p class="mb-0">{{ $cms['footer_copyright'] ?? ('&copy; ' . date('Y') . ' ' . $sysName . '. All rights reserved.') }}</p>
                <p class="mb-0">Engineered for High-Performance Educational Institutions.</p>
            </div>
        </div>
    </footer>

    <style>
        .hover-light:hover {
            color: #38bdf8 !important;
            text-decoration: none;
        }
    </style>
</body>

</html>
