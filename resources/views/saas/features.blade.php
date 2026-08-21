@extends('layouts.saas_master')

@section('page_title', 'Platform Features & Capabilities')

@section('content')
<div class="container my-5 py-4">
    <div class="text-center mb-5">
        <span class="badge badge-pill badge-info px-3 py-1 mb-2 font-weight-bold">ENTERPRISE CAPABILITIES</span>
        <h1 class="text-white font-weight-bold display-4">Engineered for Academic Excellence</h1>
        <p class="lead text-muted max-w-2xl mx-auto">A unified, modular school management operating system built to automate administration, continuous assessment, and fee collection.</p>
    </div>

    <div class="row">
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="feature-card h-100">
                <div class="rounded-circle d-inline-flex p-3 mb-3" style="background: rgba(14, 165, 233, 0.15);">
                    <i class="material-symbols-rounded text-info" style="font-size: 32px;">calculate</i>
                </div>
                <h5 class="text-white font-weight-bold mb-2">Continuous Assessment (CA1, CA2 &amp; Exam)</h5>
                <p class="text-muted font-size-sm">
                    Subject teachers enter CA 1, CA 2, and Exam scores with instant validation. Exam officers review, moderate, and approve scores with single-click audit workflows.
                </p>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <div class="feature-card h-100">
                <div class="rounded-circle d-inline-flex p-3 mb-3" style="background: rgba(34, 197, 94, 0.15);">
                    <i class="material-symbols-rounded text-success" style="font-size: 32px;">payments</i>
                </div>
                <h5 class="text-white font-weight-bold mb-2">Bursary &amp; Automated Fee Lockdown</h5>
                <p class="text-muted font-size-sm">
                    Invoice entire cohorts or individual students. Track outstanding balances, issue payment receipts, and automatically lock report cards for unpaid fee defaulters.
                </p>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <div class="feature-card h-100">
                <div class="rounded-circle d-inline-flex p-3 mb-3" style="background: rgba(168, 85, 247, 0.15);">
                    <i class="material-symbols-rounded text-purple" style="font-size: 32px;">badge</i>
                </div>
                <h5 class="text-white font-weight-bold mb-2">Role-Based Teacher Portals</h5>
                <p class="text-muted font-size-sm">
                    Strict segregation between Class Teachers (daily register, student records, stats) and Subject Teachers (assigned subject classes, score entry, and syllabus).
                </p>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <div class="feature-card h-100">
                <div class="rounded-circle d-inline-flex p-3 mb-3" style="background: rgba(245, 158, 11, 0.15);">
                    <i class="material-symbols-rounded text-warning" style="font-size: 32px;">sms</i>
                </div>
                <h5 class="text-white font-weight-bold mb-2">Bulk SMS &amp; Guardian Notifications</h5>
                <p class="text-muted font-size-sm">
                    Direct phone extraction for parents and guardians. Dispatch fee payment reminders, termly report card readiness notices, and holiday announcements.
                </p>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <div class="feature-card h-100">
                <div class="rounded-circle d-inline-flex p-3 mb-3" style="background: rgba(239, 68, 68, 0.15);">
                    <i class="material-symbols-rounded text-danger" style="font-size: 32px;">history_edu</i>
                </div>
                <h5 class="text-white font-weight-bold mb-2">WAEC / NECO Secondary Scale</h5>
                <p class="text-muted font-size-sm">
                    Supports Nigerian secondary standards (JSS 1-3 &amp; SS 1-3) with standard letter grading (A1 to F9) and automatic tie-handling class ranking algorithms.
                </p>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <div class="feature-card h-100">
                <div class="rounded-circle d-inline-flex p-3 mb-3" style="background: rgba(56, 189, 248, 0.15);">
                    <i class="material-symbols-rounded text-info" style="font-size: 32px;">database</i>
                </div>
                <h5 class="text-white font-weight-bold mb-2">100% Isolated Multi-Tenancy</h5>
                <p class="text-muted font-size-sm">
                    Complete data isolation with dedicated MySQL databases per school, SSL encryption, Google 2-Factor Authentication, and daily automated cloud snapshots.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
