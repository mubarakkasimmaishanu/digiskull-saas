@extends('layouts.master')
@section('page_title', 'Score Submissions Moderation')
@section('content')

<div class="card shadow-sm">
    <div class="card-header bg-white header-elements-inline py-3 border-bottom">
        <div>
            <h5 class="card-title font-weight-bold mb-1 text-primary">
                <i class="icon-stack mr-2"></i> Score Submissions &amp; Moderation
            </h5>
            <span class="text-muted small">
                Review submitted score packages, approve marks, return for correction, or toggle locks.
            </span>
        </div>
        <div class="header-elements">
            <a href="{{ route('exam_officer.dashboard') }}" class="btn btn-sm btn-light border mr-2">
                <i class="icon-arrow-left52 mr-1"></i> Dashboard
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card-body bg-light border-bottom py-3">
        <form method="GET" action="{{ route('exam_officer.submissions') }}">
            <div class="row align-items-end">
                <div class="col-md-3 mb-2">
                    <label class="font-weight-semibold font-size-xs text-muted text-uppercase mb-1">Examination</label>
                    <select name="exam_id" class="form-control form-control-sm">
                        <option value="">All Exams</option>
                        @foreach($exams as $ex)
                            <option value="{{ $ex->id }}" {{ $selected_exam == $ex->id ? 'selected' : '' }}>
                                {{ $ex->name }} ({{ $ex->year }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-2">
                    <label class="font-weight-semibold font-size-xs text-muted text-uppercase mb-1">Class</label>
                    <select name="class_id" class="form-control form-control-sm" id="filter_class">
                        <option value="">All Classes</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $selected_class == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-2">
                    <label class="font-weight-semibold font-size-xs text-muted text-uppercase mb-1">Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">All Statuses</option>
                        <option value="submitted" {{ $selected_status == 'submitted' ? 'selected' : '' }}>Submitted (Pending Review)</option>
                        <option value="approved" {{ $selected_status == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="locked" {{ $selected_status == 'locked' ? 'selected' : '' }}>Locked</option>
                        <option value="rejected" {{ $selected_status == 'rejected' ? 'selected' : '' }}>Returned / Rejected</option>
                        <option value="draft" {{ $selected_status == 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>

                <div class="col-md-3 mb-2 d-flex">
                    <button type="submit" class="btn btn-sm btn-primary font-weight-semibold mr-2 flex-grow-1">
                        <i class="icon-filter3 mr-1"></i> Filter
                    </button>
                    <a href="{{ route('exam_officer.submissions') }}" class="btn btn-sm btn-light border">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Submissions Table --}}
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered mb-0">
            <thead class="bg-light text-muted font-size-xs text-uppercase">
                <tr>
                    <th style="width: 45px;" class="text-center">#</th>
                    <th>Exam</th>
                    <th>Class &amp; Section</th>
                    <th>Subject</th>
                    <th>Subject Teacher</th>
                    <th>Status</th>
                    <th>Submitted / Reviewed</th>
                    <th class="text-center" style="width: 220px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions as $idx => $sub)
                    <tr>
                        <td class="text-center text-muted font-weight-semibold">{{ $submissions->firstItem() + $idx }}</td>
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
                        <td class="small text-muted">
                            @if($sub->submitted_at)
                                Sub: {{ $sub->submitted_at->format('M d, h:i A') }}<br>
                            @endif
                            @if($sub->reviewed_at)
                                Rev: {{ $sub->reviewed_at->format('M d, h:i A') }}
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="{{ route('exam_officer.review', $sub->id) }}" class="btn btn-xs btn-primary font-weight-semibold shadow-sm mr-1" title="Review Scores">
                                    <i class="icon-eye mr-1"></i> Review
                                </a>

                                @if(!$sub->isApproved() && !$sub->isLocked())
                                    <form method="POST" action="{{ route('exam_officer.approve', $sub->id) }}" class="d-inline" onsubmit="return confirm('Approve this score package?');">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-success mr-1 shadow-sm" title="Approve Scores">
                                            <i class="icon-check"></i>
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-xs btn-danger mr-1 shadow-sm" data-toggle="modal" data-target="#rejectModal-{{ $sub->id }}" title="Return for correction">
                                        <i class="icon-cross2"></i>
                                    </button>
                                @endif

                                <form method="POST" action="{{ route('exam_officer.toggle_lock', $sub->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-xs {{ $sub->isLocked() ? 'btn-dark' : 'btn-outline-dark' }}" title="{{ $sub->isLocked() ? 'Unlock Package' : 'Lock Package' }}">
                                        <i class="{{ $sub->isLocked() ? 'icon-lock' : 'icon-unlocked' }}"></i>
                                    </button>
                                </form>
                            </div>

                            {{-- Rejection Modal --}}
                            <div class="modal fade text-left" id="rejectModal-{{ $sub->id }}" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('exam_officer.reject', $sub->id) }}">
                                            @csrf
                                            <div class="modal-header bg-danger text-white">
                                                <h6 class="modal-title font-weight-bold">
                                                    <i class="icon-warning2 mr-1"></i> Return Scores for Correction
                                                </h6>
                                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="text-muted small">
                                                    Returning <strong>{{ $sub->subject->name }}</strong> ({{ $sub->my_class->name }} {{ $sub->section->name }}) to the teacher will reopen score editing.
                                                </p>
                                                <div class="form-group">
                                                    <label class="font-weight-semibold">Reason / Required Corrections:</label>
                                                    <textarea name="rejection_reason" rows="3" class="form-control" placeholder="e.g. Please verify CA 2 scores for Amina Sani..." required></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger font-weight-semibold">
                                                    <i class="icon-undo2 mr-1"></i> Return to Teacher
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="icon-stack icon-2x mb-2 text-muted"></i>
                            <p class="mb-0">No score submissions found matching the selected criteria.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($submissions->hasPages())
        <div class="card-footer bg-white border-top d-flex justify-content-end py-2">
            {{ $submissions->links() }}
        </div>
    @endif
</div>

@endsection
