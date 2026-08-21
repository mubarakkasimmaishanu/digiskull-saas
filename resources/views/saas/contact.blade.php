@extends('layouts.saas_master')

@section('page_title', 'Contact Our Solutions Team')

@section('content')
<div class="container my-5 py-4">
    <div class="row">
        {{-- Contact Info Cards --}}
        <div class="col-lg-5 mb-4 mb-lg-0">
            <div class="glass-card p-4 p-md-5 h-100 shadow-2xl">
                <span class="badge badge-pill badge-info px-3 py-1 mb-3 font-weight-bold">DIRECT CONTACT</span>
                <h3 class="text-white font-weight-bold mb-4">We are here to assist your school</h3>
                <p class="text-muted mb-4 font-size-sm">
                    Have questions about portal setup, student data migration, WAEC/NECO custom grading configurations, or staff onboarding? Our dedicated support engineers are available.
                </p>

                <div class="d-flex align-items-start mb-4">
                    <div class="rounded-circle p-2 mr-3 bg-info text-white">
                        <i class="material-symbols-rounded">mail</i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Support Email</span>
                        <a href="mailto:{{ $cms['contact_email'] ?? 'support@digiskul.app' }}" class="text-white font-weight-bold">{{ $cms['contact_email'] ?? 'support@digiskul.app' }}</a>
                    </div>
                </div>

                <div class="d-flex align-items-start mb-4">
                    <div class="rounded-circle p-2 mr-3 bg-success text-white">
                        <i class="material-symbols-rounded">call</i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Phone Support</span>
                        <a href="tel:{{ $cms['contact_phone'] ?? '+234 800 000 0000' }}" class="text-white font-weight-bold">{{ $cms['contact_phone'] ?? '+234 800 000 0000' }}</a>
                    </div>
                </div>

                @if(!empty($cms['contact_whatsapp']))
                <div class="d-flex align-items-start mb-4">
                    <div class="rounded-circle p-2 mr-3 bg-primary text-white">
                        <i class="material-symbols-rounded">chat</i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">WhatsApp Instant Desk</span>
                        <span class="text-white font-weight-bold">{{ $cms['contact_whatsapp'] }}</span>
                    </div>
                </div>
                @endif

                <div class="d-flex align-items-start">
                    <div class="rounded-circle p-2 mr-3 bg-warning text-white">
                        <i class="material-symbols-rounded">location_on</i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Head Office</span>
                        <span class="text-white font-size-sm">{{ $cms['contact_address'] ?? 'No. 183 Tukur-Tukur, Zaria, Kaduna State, Nigeria' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact Form --}}
        <div class="col-lg-7">
            <div class="glass-card p-4 p-md-5 shadow-2xl">
                <div class="mb-4">
                    <h3 class="text-white font-weight-bold">Send Us a Direct Message</h3>
                    <p class="text-muted font-size-sm">Fill in your inquiry details below and our team will get back to you within 2 business hours.</p>
                </div>

                <form action="#" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="text-white font-weight-semibold font-size-sm">Your Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg bg-dark text-white" name="name" required placeholder="e.g. Alh. Usman Danjuma">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="text-white font-weight-semibold font-size-sm">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control form-control-lg bg-dark text-white" name="email" required placeholder="e.g. usman@school.edu.ng">
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="text-white font-weight-semibold font-size-sm">Subject <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg bg-dark text-white" name="subject" required placeholder="e.g. School Onboarding Inquiry">
                    </div>

                    <div class="form-group mb-4">
                        <label class="text-white font-weight-semibold font-size-sm">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control bg-dark text-white" name="message" rows="5" required placeholder="Provide details about your inquiry..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-gradient-primary btn-lg btn-block py-3 rounded-pill font-weight-bold shadow-lg">
                        <i class="material-symbols-rounded align-middle mr-1">send</i> Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
