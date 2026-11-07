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
        --primary-blue: #2563eb;
        --primary-blue-hover: #1d4ed8;
    }

    html, body {
        color-scheme: dark;
        background-color: #0b0f19 !important;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        color: #cbd5e1;
        min-height: 100%;
        margin: 0;
        padding: 0;
    }

    body.login-layout-body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        background-color: #0b0f19 !important;
    }

    div.page-content.login-cover {
        background-image: none !important;
        background: #0b0f19 !important;
        flex: 1 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        padding: 2.5rem 1rem;
        width: 100%;
    }

    .text-color-custom {
        color: #ffffff !important;
    }

    .navbar.login {
        background-color: rgba(11, 15, 25, 0.95) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        z-index: 10;
    }

    .navbar-expand-lg.login {
        background-color: rgba(11, 15, 25, 0.95) !important;
        border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        z-index: 10;
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
        font-weight: 500;
        backdrop-filter: blur(8px);
        transition: all 0.2s ease;
    }

    .btn-outline-glass:hover {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.2);
        color: #ffffff !important;
        transform: translateY(-1px);
    }

    .glass-card-login {
        background: rgba(20, 27, 45, 0.85) !important;
        border: 1px solid rgba(255, 255, 255, 0.09) !important;
        border-radius: 20px !important;
        box-shadow: 0 20px 45px -15px rgba(0, 0, 0, 0.7), 0 0 1px 1px rgba(255, 255, 255, 0.05) !important;
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
    }

    .login-form-input {
        background-color: rgba(15, 23, 42, 0.7) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        color: #f8fafc !important;
        height: 46px !important;
        border-radius: 10px !important;
        font-size: 0.925rem !important;
        transition: all 0.2s ease;
    }

    .login-form-input:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2) !important;
        background-color: rgba(15, 23, 42, 0.95) !important;
    }

    .login-input-addon {
        background-color: rgba(15, 23, 42, 0.8) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        border-right: none !important;
        color: #94a3b8 !important;
        border-top-left-radius: 10px !important;
        border-bottom-left-radius: 10px !important;
        padding-left: 14px;
        padding-right: 14px;
    }

    .login-input-addon + .login-form-input {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
        border-left: none !important;
    }
</style>