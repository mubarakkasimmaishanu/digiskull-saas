@extends('layouts.master')
@section('page_title', 'Examination Officer Workspace')
@section('content')

{{-- Welcome Header --}}
<div class="card bg-slate-900 text-white shadow-sm border-0 mb-4" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h4 class="font-weight-bold mb-1 text-white">
                    <i class="icon-graduation2 mr-2 text-primary"></i> Examination Officer &amp; Academic Assessment Hub
                </h4>
                <p class="mb-0 text-slate-300">
                    Review score submissions, audit missing marks, approve &amp; lock records, compute rankings, and generate report cards.
                </p>
            </div>
            <div class="mt-2 mt-md-0">
                <span class="badge badge-primary px-3 py-2 font-size-sm">Session: {{ $year }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Metric Cards --}}
<div class="row">
    <div class="col-sm-6 col-xl-3">
        <div class="card card-body bg-white shadow-sm border-left-3 border-left-info">
            <div class="media">
                <div class="media-body">
                    <h3 class="font-weight-bold mb-0 text-info">{{ $submissions_count['submitted'] }}</h3>
                    <span class="text-uppercase font-size-xs text-muted font-weight-semibold">Pending Review</span>
                </div>
                <div class="ml-3 align-self-center">
                    <i class="icon-inbox icon-2x text-info"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card card-body bg-white shadow-sm border-left-3 border-left-success">
            <div class="media">
                <div class="media-body">
                    <h3 class="font-weight-bold mb-0 text-success">{{ $submissions_count['approved'] }}</h3>
                    <span class="text-uppercase font-size-xs text-muted font-weight-semibold">Approved Packages</span>
                </div>
                <div class="ml-3 align-self-center">
                    <i class="icon-checkmark-circle icon-2x text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card card-body bg-white shadow-sm border-left-3 border-left-warning">
            <div class="media">
                <div class="media-body">
                    <h3 class="font-weight-bold mb-0 text-warning">{{ $submissions_count['draft'] + $submissions_count['rejected'] }}</h3>
                    <span class="text-uppercase font-size-xs text-muted font-weight-semibold">Drafts / Returned</span>
                </div>
                <div class="ml-3 align-self-center">
                    <i class="icon-pencil5 icon-2x text-warning"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card card-body bg-white shadow-sm border-left-3 border-left-dark">
            <div class="media">
                <div class="media-body">
                    <h3 class="font-weight-bold mb-0 text-dark">{{ $submissions_count['locked'] }}</h3>
                    <span class="text-uppercase font-size-xs text-muted font-weight-semibold">Locked Packages</span>
                </div>
                <div class="ml-3 align-self-center">
                    <i class="icon-lock icon-2x text-dark"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Actions Hub --}}
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white font-weight-bold py-3 border-bottom text-primary">
                <i class="icon-stack mr-2"></i> Score Moderation &amp; Submissions
            </div>
            <div class="card-body">
                <p class="text-muted small">
                    Review incoming subject score submissions from subject teachers, verify CA1, CA2, and exam entries, and approve or return for correction.
                </p>
                <a href="{{ route('exam_officer.submissions') }}" class="btn btn-primary btn-block font-weight-semibold shadow-sm">
                    <i class="icon-list3 mr-1"></i> Review Submissions
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white font-weight-bold py-3 border-bottom text-danger">
                <i class="icon-warning2 mr-2"></i> Missing Scores Auditor
            </div>
            <div class="card-body">
                <p class="text-muted small">
                    Audit incomplete student scores, identify unsubmitted subjects across classes, and ensure 100% data completeness before results publication.
                </p>
                <a href="{{ route('exam_officer.missing_scores') }}" class="btn btn-outline-danger btn-block font-weight-semibold">
                    <i class="icon-shield-notice mr-1"></i> Audit Incomplete Scores
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white font-weight-bold py-3 border-bottom text-success">
                <i class="icon-calculator mr-2"></i> Results &amp; Report Cards
            </div>
            <div class="card-body">
                <p class="text-muted small">
                    Compute overall subject positions, student averages, overall class &amp; section positions, and generate comprehensive printable report cards.
                </p>
                <a href="{{ route('exam_officer.results_generation') }}" class="btn btn-success btn-block font-weight-semibold shadow-sm">
                    <i class="icon-file-text mr-1"></i> Results &amp; Report Cards
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Recent Submissions --}}
<div class="card shadow-sm mt-4">
    <div class="card-header bg-white header-elements-inline py-3 border-bottom">
        <h5 class="card-title font-weight-bold mb-0 text-dark">
            <i class="icon-history mr-2 text-primary"></i> Recent Score Submissions Activity
        </h5>
        <a href="{{ route('exam_officer.submissions') }}" class="btn btn-sm btn-light border">
            View All Submissions &rarr;
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-striped mb-0">
            <thead class="bg-light text-muted font-size-xs text-uppercase">
                <tr>
                    <th>Exam</th>
                    <th>Class &amp; Section</th>
                    <th>Subject</th>
                    <th>Teacher</th>
                    <th>Status</th>
                    <th>Last Updated</th>
                    <th class="text-center" style="width: 130px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recent_submissions as $sub)
                    <tr>
                        <td class="font-weight-semibold">{{ $sub->exam->name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge badge-light border">{{ $sub->my_class->name ?? 'N/A' }}</span>
                            <span class="badge badge-secondary">{{ $sub->section->name ?? 'N/A' }}</span>
                        </td>
                        <td class="font-weight-bold text-primary">{{ $sub->subject->name ?? 'N/A' }}</td>
                        <td>{{ $sub->teacher->name ?? 'Unassigned' }}</td>
                        <td>
                            @if($sub->isApproved())
                                <span class="badge badge-success px-2 py-1"><i class="icon-check mr-1"></i> Approved</span>
                            @elseif($sub->isLocked())
                                <span class="badge badge-dark px-2 py-1"><i class="icon-lock mr-1"></i> Locked</span>
                            @elseif($sub->isSubmitted())
                                <span class="badge badge-info px-2 py-1"><i class="icon-paperplane mr-1"></i> Submitted</span>
                            @elseif($sub->isRejected())
                                <span class="badge badge-danger px-2 py-1"><i class="icon-warning mr-1"></i> Returned</span>
                            @else
                                <span class="badge badge-warning px-2 py-1"><i class="icon-pencil mr-1"></i> Draft</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $sub->updated_at->diffForHumans() }}</td>
                        <td class="text-center">
                            <a href="{{ route('exam_officer.review', $sub->id) }}" class="btn btn-xs btn-primary font-weight-semibold shadow-sm">
                                <i class="icon-eye mr-1"></i> Review
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No recent score submissions recorded for academic session {{ $year }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
