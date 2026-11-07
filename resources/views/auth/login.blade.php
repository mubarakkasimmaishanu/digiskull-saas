@extends('layouts.login_master')

@section('page_title', 'Account Login')

@section('content')

<div class="page-content login-cover w-100">
    <div class="w-100" style="max-width: 440px; position: relative; z-index: 1;">
        
        <!-- Login card -->
        <form class="login-form" method="post" action="{{ route('login') }}">
            @csrf
            <div class="glass-card-login p-4 p-md-4">
                
                {{-- Header --}}
                <div class="text-center mb-4">
                    <div class="rounded-circle d-inline-flex p-3 mb-2" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                        <span class="material-symbols-rounded text-white" style="font-size: 26px;">lock</span>
                    </div>
                    <h4 class="text-white font-weight-bold mb-1">Welcome Back</h4>
                    <p class="text-muted font-size-sm mb-0">Enter your credentials to access your school portal</p>
                </div>

                {{-- Alerts --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible bg-danger-alpha text-white border-0 py-2 px-3 font-size-sm mb-3" style="border-radius: 10px;">
                        <button type="button" class="close text-white" data-dismiss="alert"><span>&times;</span></button>
                        <span>{!! implode('<br>', $errors->all()) !!}</span>
                    </div>
                @endif

                @if (session("session_expired") && $errors->isEmpty())
                    <div class="alert alert-warning alert-dismissible bg-warning-alpha text-white border-0 py-2 px-3 font-size-sm mb-3" style="border-radius: 10px;">
                        <button type="button" class="close text-white" data-dismiss="alert"><span>&times;</span></button>
                        <span>{{ session("session_expired") }}</span>
                    </div>
                @endif

                @if (Session::has("access_denied"))
                    <div class="alert alert-danger alert-dismissible bg-danger-alpha text-white border-0 py-2 px-3 font-size-sm mb-3" style="border-radius: 10px;">
                        <button type="button" class="close text-white" data-dismiss="alert"><span>&times;</span></button>
                        <span>{{ session("access_denied") }}</span>
                    </div>
                @endif

                {{-- Identity / Username Input --}}
                <div class="form-group mb-3">
                    <label class="text-light font-weight-semibold font-size-sm mb-1.5 d-block">Username / Email / ID</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text login-input-addon">
                                <i class="material-symbols-rounded font-size-sm">person</i>
                            </span>
                        </div>
                        <input type="text" class="form-control login-form-input" name="identity" value="{{ old('identity') }}" placeholder="e.g. teacher1 or user@school.ng" autocomplete="identity" required autofocus>
                    </div>
                </div>

                {{-- Password Input --}}
                <div class="form-group mb-3">
                    <label class="text-light font-weight-semibold font-size-sm mb-1.5 d-block">Password</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text login-input-addon">
                                <i class="material-symbols-rounded font-size-sm">key</i>
                            </span>
                        </div>
                        <input type="password" id="login_password" class="form-control login-form-input" required name="password" placeholder="Enter your password" autocomplete="current-password" style="border-top-right-radius: 0 !important; border-bottom-right-radius: 0 !important;">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-dark text-muted px-3 d-flex align-items-center" onclick="togglePasswordVisibility()" style="border: 1px solid rgba(255, 255, 255, 0.12); border-left: none; border-top-right-radius: 10px; border-bottom-right-radius: 10px; background: rgba(15, 23, 42, 0.8);">
                                <i class="material-symbols-rounded font-size-sm" id="pw_toggle_icon">visibility</i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Remember Me & Forgot Password --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="rememberMeCheck" name="remember" @checked(old('remember'))>
                        <label class="custom-control-label text-muted font-size-sm cursor-pointer" for="rememberMeCheck">Remember me</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="text-info font-size-sm hover-underline">Forgot password?</a>
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="btn btn-gradient-primary btn-block py-2.5 rounded-pill font-weight-bold d-flex justify-content-center align-items-center" style="height: 46px;">
                    <span>Sign in to Portal</span>
                    <i class="material-symbols-rounded ml-2 font-size-sm">arrow_forward</i>
                </button>
            </div>
        </form>

    </div>
</div>

<style>
    .hover-underline:hover {
        text-decoration: underline !important;
        color: #60a5fa !important;
    }
</style>

<script>
    function togglePasswordVisibility() {
        var pwInput = document.getElementById('login_password');
        var pwIcon = document.getElementById('pw_toggle_icon');
        if (pwInput.type === 'password') {
            pwInput.type = 'text';
            pwIcon.textContent = 'visibility_off';
        } else {
            pwInput.type = 'password';
            pwIcon.textContent = 'visibility';
        }
    }
</script>

@endsection
