@extends('layouts.login_master')

@section('page_title', 'Password Recovery')

@section('content')

<div class="page-content login-cover w-100">
    <div class="w-100" style="max-width: 580px; position: relative; z-index: 1;">

        <!-- Password recovery form -->
        <form class="login-form" method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="glass-card-login p-4 px-md-5 py-md-4">

                <div class="text-center mb-3">
                    <div class="rounded-circle d-inline-flex p-2 mb-2" style="background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.3);">
                        <span class="material-symbols-rounded text-info" style="font-size: 26px;">lock_reset</span>
                    </div>
                    <h4 class="text-white font-weight-bold mb-1">Password Recovery</h4>
                    <p class="text-muted font-size-xs mb-0">We will send reset instructions to your registered email</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-success alert-styled-left alert-dismissible bg-success-alpha text-white border-0 py-1 px-3 font-size-xs mb-2">
                        <button type="button" class="close text-white" data-dismiss="alert"><span>&times;</span></button>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif
                @if ($errors->has('email'))
                    <div class="alert alert-danger alert-styled-left alert-dismissible bg-danger-alpha text-white border-0 py-1 px-3 font-size-xs mb-2">
                        <button type="button" class="close text-white" data-dismiss="alert"><span>&times;</span></button>
                        <span>{{ $errors->first('email') }}</span>
                    </div>
                @endif

                <div class="form-group mb-3">
                    <label class="text-light font-weight-semibold font-size-xs mb-1">Email Address</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-dark border-secondary text-muted py-1" style="border-color: rgba(255, 255, 255, 0.12) !important;">
                                <i class="material-symbols-rounded font-size-sm">mail</i>
                            </span>
                        </div>
                        <input name="email" required type="email" class="form-control bg-dark text-white font-size-sm border-secondary py-2" style="border-color: rgba(255, 255, 255, 0.12) !important; height: auto;" value="{{ old('email') }}" placeholder="e.g. user@school.ng" autofocus>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted font-size-xs">Lost access to email? <a href="javascript:;" class="text-info hover-underline" id="contact-link" data-toggle="modal" data-target="#admin-contact-modal">Contact Admin</a></span>
                    <a href="{{ route('login') }}" class="text-muted font-size-xs hover-underline">
                        <i class="material-symbols-rounded font-size-xs align-middle mr-1">arrow_back</i> Back to Login
                    </a>
                </div>

                <button type="submit" class="btn btn-gradient-primary btn-block py-2 rounded-pill font-weight-bold shadow-lg d-flex justify-content-center align-items-center">
                    <span>Send Recovery Link</span>
                    <i class="material-symbols-rounded ml-2 font-size-sm">send</i>
                </button>
            </div>
        </form>

    </div>
</div>

@endsection
