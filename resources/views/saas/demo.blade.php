@extends('layouts.saas_master')

@section('page_title', 'Request a Personalized Live Demo')

@section('content')
<div class="container my-5 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="glass-card p-4 p-md-5 shadow-2xl">
                <div class="text-center mb-4">
                    <span class="badge badge-pill badge-info px-3 py-1 mb-2 font-weight-bold">PRODUCT TOUR</span>
                    <h2 class="text-white font-weight-bold display-5">Schedule a Live Demo</h2>
                    <p class="text-muted max-w-lg mx-auto">See how Digiskul streamlines your continuous assessments, bursary fee collections, and report card locking in real-time.</p>
                </div>

                <form action="{{ route('saas.demo.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="text-white font-weight-semibold">School Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg bg-dark text-white" name="school_name" required placeholder="e.g. Greenwood Academy">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="text-white font-weight-semibold">Contact Person Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg bg-dark text-white" name="contact_name" required placeholder="e.g. Principal Adams">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="text-white font-weight-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control form-control-lg bg-dark text-white" name="email" required placeholder="e.g. adams@greenwood.com">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="text-white font-weight-semibold">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg bg-dark text-white" name="phone" required placeholder="e.g. +234 803 123 4567">
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="text-white font-weight-semibold">Estimated Enrolled Students <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-lg bg-dark text-white" name="estimated_students" required placeholder="e.g. 500">
                    </div>

                    <div class="form-group mb-4">
                        <label class="text-white font-weight-semibold">Custom Requirements / Focus Areas</label>
                        <textarea class="form-control bg-dark text-white" name="message" rows="4" placeholder="Let us know if you want to focus on Bursary, CA Marks Entry, or Report Cards..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-gradient-primary btn-lg btn-block py-3 rounded-pill font-weight-bold shadow-lg">
                        <i class="material-symbols-rounded align-middle mr-1">event_available</i> Submit Demo Request
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
