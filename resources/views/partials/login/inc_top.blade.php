{{-- Favicons & Brand Icons --}}
@include('partials.fav_icons')

{{-- Fonts --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">

<!-- Global stylesheets -->
<link href="{{ asset('assets/css/bootstrap-min.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('assets/css/bootstrap_limitless-min.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('assets/css/layout-min.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('assets/css/components-min.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('assets/css/colors-min.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('assets/css/theme-dark-support.css') }}" rel="stylesheet" type="text/css">

<!-- Core JS files -->
<script src="{{ asset('global_assets/js/main/jquery.min.js') }}"></script>
<script src="{{ asset('global_assets/js/main/bootstrap.bundle.min.js') }}"></script>

<!-- Theme JS files -->
<script src="{{ asset('assets/js/app-min.js') }}"></script>
<script src="{{ asset('global_assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
<script src="{{ asset('global_assets/js/plugins/scrollers/jquery.marquee.min.js') }}"></script>
<script src="{{ asset('global_assets/js/main/color-modes.js') }}"></script>

@php
    $color = 'white !important';
    $bg_color = '#0b0f19';
@endphp

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);
    }

    html, body {
        color-scheme: dark;
        background-color: #0b0f19 !important;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        color: #cbd5e1;
        height: 100vh;
        max-height: 100vh;
        overflow: hidden;
        margin: 0;
        padding: 0;
    }

    body.login-layout-body {
        display: flex;
        flex-direction: column;
        height: 100vh;
        max-height: 100vh;
        overflow: hidden;
    }

    div.page-content.login-cover {
        background-image: none !important;
        background: #0b0f19 !important;
        flex: 1 1 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        padding: 10px 15px;
    }

    /* Ambient glow */
    div.page-content.login-cover::before {
        content: '';
        position: absolute;
        top: 40%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 600px;
        height: 380px;
        background: radial-gradient(circle, rgba(14, 165, 233, 0.12) 0%, rgba(99, 102, 241, 0.05) 50%, transparent 70%);
        pointer-events: none;
        z-index: 0;
    }

    .text-color-custom {
        color: #ffffff !important;
    }

    .navbar.login {
        background-color: rgba(11, 15, 25, 0.9) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        z-index: 10;
    }

    .navbar-expand-lg.login {
        background-color: rgba(11, 15, 25, 0.9) !important;
        border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        z-index: 10;
    }

    .btn-gradient-primary {
        background: var(--primary-gradient);
        color: #ffffff !important;
        border: none;
        font-weight: 600;
        box-shadow: 0 4px 14px 0 rgba(14, 165, 233, 0.39);
        transition: all 0.25s ease;
    }

    .btn-gradient-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(14, 165, 233, 0.5);
        color: #ffffff !important;
    }

    .btn-outline-glass {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #ffffff !important;
        font-weight: 500;
        backdrop-filter: blur(8px);
        transition: all 0.25s ease;
    }

    .btn-outline-glass:hover {
        background: rgba(255, 255, 255, 0.12);
        border-color: rgba(255, 255, 255, 0.3);
        color: #ffffff !important;
    }

    .glass-card-login {
        background: rgba(30, 41, 59, 0.7) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 18px !important;
        box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.6) !important;
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
    }
</style>