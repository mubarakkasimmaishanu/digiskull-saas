@extends('layouts.login_master')

@section('page_title', 'Account Login')

@section('content')

<div class="page-content login-cover w-100">
    <div class="w-100" style="max-width: 580px; position: relative; z-index: 1;">
        
        <!-- Login card -->
        <form class="login-form" method="post" action="{{ route('login') }}">
            @csrf
            <div class="glass-card-login p-4 px-md-5 py-md-4">
                
                <div class="text-center mb-3">
                    <div class="rounded-circle d-inline-flex p-2 mb-2" style="background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.3);">
                        <span class="material-symbols-rounded text-info" style="font-size: 26px;">lock</span>
                    </div>
                    <h4 class="text-white font-weight-bold mb-1">Welcome Back</h4>
                    <p class="text-muted font-size-xs mb-0">Enter your credentials to access your school portal</p>
                </div>

                {{-- Alerts --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-styled-left alert-dismissible bg-danger-alpha text-white border-0 py-1 px-3 font-size-xs mb-2">
                        <button type="button" class="close text-white" data-dismiss="alert"><span>&times;</span></button>
                        <span>{!! implode('<br>', $errors->all()) !!}</span>
                    </div>
                @endif

                @if (session("session_expired") && $errors->isEmpty())
                    <div class="alert alert-warning alert-styled-left alert-dismissible bg-warning-alpha text-white border-0 py-1 px-3 font-size-xs mb-2">
                        <button type="button" class="close text-white" data-dismiss="alert"><span>&times;</span></button>
                        <span>{{ session("session_expired") }}</span>
                    </div>
                @endif

                @if (Session::has("access_denied"))
                    <div class="alert alert-danger alert-styled-left alert-dismissible bg-danger-alpha text-white border-0 py-1 px-3 font-size-xs mb-2">
                        <button type="button" class="close text-white" data-dismiss="alert"><span>&times;</span></button>
                        <span>{{ session("access_denied") }}</span>
                    </div>
                @endif

                {{-- Identity / Username Input --}}
                <div class="form-group mb-3">
                    <label class="text-light font-weight-semibold font-size-xs mb-1">Username / Email / ID</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-dark border-secondary text-muted py-1" style="border-color: rgba(255, 255, 255, 0.12) !important;">
                                <i class="material-symbols-rounded font-size-sm">person</i>
                            </span>
                        </div>
                        <input type="text" class="form-control bg-dark text-white font-size-sm border-secondary py-2" style="border-color: rgba(255, 255, 255, 0.12) !important; height: auto;" name="identity" value="{{ old('identity') }}" placeholder="e.g. teacher1 or user@school.ng" autocomplete="identity" required autofocus>
                    </div>
                </div>

                {{-- Password Input --}}
                <div class="form-group mb-3">
                    <label class="text-light font-weight-semibold font-size-xs mb-1">Password</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-dark border-secondary text-muted py-1" style="border-color: rgba(255, 255, 255, 0.12) !important;">
                                <i class="material-symbols-rounded font-size-sm">key</i>
                            </span>
                        </div>
                        <input type="password" class="form-control bg-dark text-white font-size-sm border-secondary py-2" style="border-color: rgba(255, 255, 255, 0.12) !important; height: auto;" required name="password" placeholder="Enter password" autocomplete="current-password">
                    </div>
                </div>

                {{-- Remember Me & Forgot Password --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="rememberMeCheck" name="remember" @checked(old('remember'))>
                        <label class="custom-control-label text-muted font-size-xs" for="rememberMeCheck">Remember me</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="text-info font-size-xs hover-underline">Forgot password?</a>
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="btn btn-gradient-primary btn-block py-2 rounded-pill font-weight-bold shadow-lg d-flex justify-content-center align-items-center">
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
        color: #38bdf8 !important;
    }
</style>

@endsection
