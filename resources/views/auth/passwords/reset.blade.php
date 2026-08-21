@extends('layouts.login_master')

@section('page_title', 'Set New Password')

@section('content')

<div class="page-content login-cover w-100">
    <div class="w-100" style="max-width: 580px; position: relative; z-index: 1;">
        <div class="glass-card-login p-4 px-md-5 py-md-4">
            
            <div class="text-center mb-3">
                <div class="rounded-circle d-inline-flex p-2 mb-2" style="background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.3);">
                    <span class="material-symbols-rounded text-info" style="font-size: 26px;">lock_open</span>
                </div>
                <h4 class="text-white font-weight-bold mb-1">Set New Password</h4>
                <p class="text-muted font-size-xs mb-0">Create a secure password to access your account</p>
            </div>

            @if($errors->has('token'))
                <div class="alert alert-danger alert-styled-left alert-dismissible bg-danger-alpha text-white border-0 py-1 px-3 font-size-xs mb-2">
                    <button type="button" class="close text-white" data-dismiss="alert"><span>&times;</span></button>
                    <span>{{ $errors->first('token') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group mb-2">
                    <label class="text-light font-weight-semibold font-size-xs mb-1">Email Address</label>
                    <input id="email" type="email" class="form-control bg-dark text-white font-size-sm border-secondary{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ $email ?? old('email') }}" required autofocus style="border-color: rgba(255, 255, 255, 0.12) !important; height: auto; padding: 7px 12px;">
                    @if ($errors->has('email'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label class="text-light font-weight-semibold font-size-xs mb-1">New Password</label>
                        <input id="password" type="password" class="form-control bg-dark text-white font-size-sm border-secondary{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required placeholder="Min. 8 characters" style="border-color: rgba(255, 255, 255, 0.12) !important; height: auto; padding: 7px 12px;">
                        @if ($errors->has('password'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('password') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="col-md-6 form-group mb-3">
                        <label class="text-light font-weight-semibold font-size-xs mb-1">Confirm Password</label>
                        <input id="password-confirm" type="password" class="form-control bg-dark text-white font-size-sm border-secondary" name="password_confirmation" required placeholder="Re-enter password" style="border-color: rgba(255, 255, 255, 0.12) !important; height: auto; padding: 7px 12px;">
                    </div>
                </div>

                <button type="submit" class="btn btn-gradient-primary btn-block py-2 rounded-pill font-weight-bold shadow-lg d-flex justify-content-center align-items-center">
                    <span>Update &amp; Save Password</span>
                    <i class="material-symbols-rounded ml-2 font-size-sm">check_circle</i>
                </button>
            </form>
        </div>
    </div>
</div>

@endsection
