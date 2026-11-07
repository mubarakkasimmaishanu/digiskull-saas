@extends('layouts.login_master')

@section('page_title', 'Password Recovery')

@section('content')

<div class="page-content login-cover w-100">
    <div class="w-100" style="max-width: 440px; position: relative; z-index: 1;">

        <!-- Password recovery form -->
        <form class="login-form" method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="glass-card-login p-4 p-md-4">

                <div class="text-center mb-4">
                    <div class="rounded-circle d-inline-flex p-3 mb-2" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                        <span class="material-symbols-rounded text-white" style="font-size: 26px;">lock_reset</span>
                    </div>
                    <h4 class="text-white font-weight-bold mb-1">Password Recovery</h4>
                    <p class="text-muted font-size-sm mb-0">We will send reset instructions to your registered email</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-success alert-dismissible bg-success-alpha text-white border-0 py-2 px-3 font-size-sm mb-3" style="border-radius: 10px;">
                        <button type="button" class="close text-white" data-dismiss="alert"><span>&times;</span></button>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif
                @if ($errors->has('email'))
                    <div class="alert alert-danger alert-dismissible bg-danger-alpha text-white border-0 py-2 px-3 font-size-sm mb-3" style="border-radius: 10px;">
                        <button type="button" class="close text-white" data-dismiss="alert"><span>&times;</span></button>
                        <span>{{ $errors->first('email') }}</span>
                    </div>
                @endif

                <div class="form-group mb-3">
                    <label class="text-light font-weight-semibold font-size-sm mb-1.5 d-block">Email Address</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text login-input-addon">
                                <i class="material-symbols-rounded font-size-sm">mail</i>
                            </span>
                        </div>
                        <input name="email" required type="email" class="form-control login-form-input" value="{{ old('email') }}" placeholder="e.g. user@school.ng" autofocus>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="javascript:;" class="text-muted font-size-xs hover-underline" id="contact-link" data-toggle="modal" data-target="#admin-contact-modal">Lost email? Contact Admin</a>
                    <a href="{{ route('login') }}" class="text-info font-size-xs hover-underline d-flex align-items-center">
                        <i class="material-symbols-rounded font-size-xs mr-1">arrow_back</i> Back to Login
                    </a>
                </div>

                <button type="submit" class="btn btn-gradient-primary btn-block py-2.5 rounded-pill font-weight-bold d-flex justify-content-center align-items-center" style="height: 46px;">
                    <span>Send Recovery Link</span>
                    <i class="material-symbols-rounded ml-2 font-size-sm">send</i>
                </button>
            </div>
        </form>

    </div>
</div>

@endsection
