@extends('layouts.login_master')

@section('page_title', 'Set New Password')

@section('content')

<div class="page-content login-cover w-100">
    <div class="w-100" style="max-width: 440px; position: relative; z-index: 1;">
        <div class="glass-card-login p-4 p-md-4">
            
            <div class="text-center mb-4">
                <div class="rounded-circle d-inline-flex p-3 mb-2" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                    <span class="material-symbols-rounded text-white" style="font-size: 26px;">lock_open</span>
                </div>
                <h4 class="text-white font-weight-bold mb-1">Set New Password</h4>
                <p class="text-muted font-size-sm mb-0">Create a secure password to access your account</p>
            </div>

            @if($errors->has('token'))
                <div class="alert alert-danger alert-dismissible bg-danger-alpha text-white border-0 py-2 px-3 font-size-sm mb-3" style="border-radius: 10px;">
                    <button type="button" class="close text-white" data-dismiss="alert"><span>&times;</span></button>
                    <span>{{ $errors->first('token') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group mb-3">
                    <label class="text-light font-weight-semibold font-size-sm mb-1.5 d-block">Email Address</label>
                    <input id="email" type="email" class="form-control login-form-input{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ $email ?? old('email') }}" required autofocus>
                    @if ($errors->has('email'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="form-group mb-3">
                    <label class="text-light font-weight-semibold font-size-sm mb-1.5 d-block">New Password</label>
                    <input id="password" type="password" class="form-control login-form-input{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required placeholder="Min. 8 characters">
                    @if ($errors->has('password'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="form-group mb-4">
                    <label class="text-light font-weight-semibold font-size-sm mb-1.5 d-block">Confirm Password</label>
                    <input id="password-confirm" type="password" class="form-control login-form-input" name="password_confirmation" required placeholder="Re-enter password">
                </div>

                <button type="submit" class="btn btn-gradient-primary btn-block py-2.5 rounded-pill font-weight-bold d-flex justify-content-center align-items-center" style="height: 46px;">
                    <span>Update &amp; Save Password</span>
                    <i class="material-symbols-rounded ml-2 font-size-sm">check_circle</i>
                </button>
            </form>
        </div>
    </div>
</div>

@endsection
