@extends('layouts.login_master')

@section('page_title', 'Two Factor Authentication')

@section('content')

<div class="page-content login-cover w-100">
    <div class="w-100" style="max-width: 440px; position: relative; z-index: 1;">
        <div class="glass-card-login p-4 p-md-4">
            
            <div class="text-center mb-4">
                <div class="rounded-circle d-inline-flex p-3 mb-2" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                    <span class="material-symbols-rounded text-white" style="font-size: 26px;">security</span>
                </div>
                <h4 class="text-white font-weight-bold mb-1">Two-Factor Security</h4>
                <p class="text-muted font-size-sm mb-0">Enter the 6-digit verification code from your Authenticator app</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible bg-danger-alpha text-white border-0 py-2 px-3 font-size-sm mb-3" style="border-radius: 10px;">
                    <button type="button" class="close text-white" data-dismiss="alert"><span>&times;</span></button>
                    <span>{!! implode('<br>', $errors->all()) !!}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('2fa.authenticate') }}">
                @csrf

                <div class="form-group mb-4 text-center">
                    <label class="text-light font-weight-semibold font-size-sm mb-2 d-block">One-Time Password (OTP)</label>
                    <input id="one_time_password" type="text" class="form-control text-white text-center font-weight-bold mx-auto login-form-input" name="one_time_password" placeholder="······" maxlength="8" required autofocus style="letter-spacing: 6px; font-size: 1.3rem; max-width: 260px; height: 50px;">
                </div>

                <button type="submit" class="btn btn-gradient-primary btn-block py-2.5 rounded-pill font-weight-bold d-flex justify-content-center align-items-center mb-3" style="height: 46px;">
                    <span>Authenticate &amp; Login</span>
                    <i class="material-symbols-rounded ml-2 font-size-sm">verified_user</i>
                </button>

                <div class="d-flex justify-content-between align-items-center">
                    @if(session('error_code') === 429)
                        <a href="{{ route('account_security.account_recovery') }}" class="text-warning font-size-xs hover-underline">Recover Account</a>
                    @else
                        <span></span>
                    @endif
                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-muted font-size-xs hover-underline">Cancel &amp; Sign Out</a>
                </div>
            </form>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </div>
</div>

@endsection
