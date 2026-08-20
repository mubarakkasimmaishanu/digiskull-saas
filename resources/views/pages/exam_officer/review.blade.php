@extends('layouts.master')
@section('page_title', 'Review Score Sheet: ' . $subject->name . ' - ' . $my_class->name . ' ' . $section->name)
@section('content')

<div class="card shadow-sm">
    <div class="card-header bg-white header-elements-inline py-3 border-bottom">
        <div>
            <h5 class="card-title font-weight-bold mb-1 text-primary">
                <i class="icon-eye mr-2"></i> Reviewing Score Sheet: {{ $subject->name }}
            </h5>
            <div class="text-muted small">
                <span class="font-weight-semibold">{{ $my_class->name }} ({{ $section->name }})</span> &bull; 
                Academic Session: <span class="badge badge-secondary">{{ $year }}</span> &bull; 
                Exam: <span class="badge badge-primary">{{ $exam->name }}</span> &bull;
                Teacher: <strong>{{ $submission->teacher->name ?? 'Unassigned' }}</strong>
            </div>
        </div>
        <div class="header-elements">
            <a href="{{ route('exam_officer.submissions') }}" class="btn btn-sm btn-light border mr-2">
                <i class="icon-arrow-left52 mr-1"></i> Back to Submissions
            </a>
        </div>
    </div>

    {{-- Status Banner & Top Moderation Bar --}}
    <div class="card-body py-2 border-bottom bg-light">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center my-1">
                <span class="text-muted mr-2 font-weight-semibold">Package Status:</span>
                @if($submission->isApproved())
                    <span class="badge badge-success px-3 py-1 font-weight-semibold" style="font-size: 13px;">
                        <i class="icon-checkmark-circle mr-1"></i> Approved
                    </span>
                @elseif($submission->isLocked())
                    <span class="badge badge-dark px-3 py-1 font-weight-semibold" style="font-size: 13px;">
                        <i class="icon-lock mr-1"></i> Locked
                    </span>
                @elseif($submission->isSubmitted())
                    <span class="badge badge-info px-3 py-1 font-weight-semibold" style="font-size: 13px;">
                        <i class="icon-paperplane mr-1"></i> Submitted (Pending Your Review)
                    </span>
                @elseif($submission->isRejected())
                    <span class="badge badge-danger px-3 py-1 font-weight-semibold" style="font-size: 13px;">
                        <i class="icon-warning2 mr-1"></i> Returned to Teacher
                    </span>
                @else
                    <span class="badge badge-warning px-3 py-1 font-weight-semibold text-dark" style="font-size: 13px;">
                        <i class="icon-pencil5 mr-1"></i> Draft
                    </span>
                @endif
            </div>

            {{-- Action Controls --}}
            <div class="d-flex align-items-center my-1">
                @if(!$submission->isApproved() && !$submission->isLocked())
                    <form method="POST" action="{{ route('exam_officer.approve', $submission->id) }}" class="d-inline mr-2">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success font-weight-semibold shadow-sm" onclick="return confirm('Approve this score package? Once approved, scores will be locked against ordinary teacher editing.');">
                            <i class="icon-checkmark-circle mr-1"></i> Approve Scores
                        </button>
                    </form>

                    <button type="button" class="btn btn-sm btn-outline-danger font-weight-semibold mr-2" data-toggle="modal" data-target="#rejectModal">
                        <i class="icon-undo2 mr-1"></i> Return for Correction
                    </button>
                @endif

                <form method="POST" action="{{ route('exam_officer.toggle_lock', $submission->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm {{ $submission->isLocked() ? 'btn-dark' : 'btn-outline-dark' }}">
                        <i class="{{ $submission->isLocked() ? 'icon-lock' : 'icon-unlocked' }} mr-1"></i>
                        {{ $submission->isLocked() ? 'Unlock' : 'Lock' }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Score Table --}}
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered mb-0">
            <thead class="bg-light text-uppercase font-size-xs text-muted">
                <tr>
                    <th style="width: 45px;" class="text-center">#</th>
                    <th style="width: 120px;">Adm No</th>
                    <th>Student Name</th>
                    <th style="width: 90px;" class="text-center">Gender</th>
                    <th style="width: 110px;" class="text-center font-weight-bold">CA 1</th>
                    <th style="width: 110px;" class="text-center font-weight-bold">CA 2</th>
                    <th style="width: 110px;" class="text-center font-weight-bold">Exam</th>
                    <th style="width: 110px;" class="text-center font-weight-bold bg-primary-100 text-primary">Total</th>
                    <th style="width: 90px;" class="text-center font-weight-bold">Grade</th>
                    <th style="width: 90px;" class="text-center font-weight-bold">Position</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $idx => $row)
                    @php
                        $stUser = $row['user'];
                        $sr = $row['student_record'];
                    @endphp
                    <tr>
                        <td class="text-center text-muted font-weight-semibold">{{ $idx + 1 }}</td>
                        <td class="font-weight-semibold text-primary">
                            <span class="badge badge-light border">{{ $sr->adm_no ?: ($stUser->username ?: '—') }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ Usr::getTenantAwarePhoto($stUser->photo) }}" class="rounded-circle mr-2 border" width="30" height="30" alt="">
                                <span class="font-weight-bold text-dark">{{ $stUser->name }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $stUser->gender === 'Female' ? 'badge-pink' : 'badge-light border' }}">
                                {{ $stUser->gender ?: '—' }}
                            </span>
                        </td>
                        <td class="text-center font-weight-semibold">{{ $row['ca1'] !== null ? $row['ca1'] : '—' }}</td>
                        <td class="text-center font-weight-semibold">{{ $row['ca2'] !== null ? $row['ca2'] : '—' }}</td>
                        <td class="text-center font-weight-semibold">{{ $row['exm'] !== null ? $row['exm'] : '—' }}</td>
                        <td class="text-center font-weight-bold font-size-base bg-primary-50 text-primary">
                            {{ $row['total'] !== null ? $row['total'] : '—' }}
                        </td>
                        <td class="text-center font-weight-bold">
                            @if($row['grade'])
                                <span class="badge badge-primary">{{ $row['grade']->name }}</span>
                            @elseif($row['total'] !== null)
                                <span class="badge badge-secondary">{{ (new \App\Repositories\AssessmentScoreRepo())->getGradeName($row['total'], $my_class->class_type_id) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center font-weight-semibold text-muted">
                            {{ $row['sub_pos'] ? Mk::getSuffix($row['sub_pos']) : '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">No student records found in this section.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Rejection Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('exam_officer.reject', $submission->id) }}">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h6 class="modal-title font-weight-bold">
                        <i class="icon-warning2 mr-1"></i> Return Scores for Correction
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">
                        Please state the specific correction or verification required for <strong>{{ $subject->name }}</strong>.
                    </p>
                    <div class="form-group">
                        <label class="font-weight-semibold">Instructions / Remarks for Teacher:</label>
                        <textarea name="rejection_reason" rows="3" class="form-control" placeholder="e.g. Please verify CA 1 score entered for Kabir..." required></textarea>
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

@endsection
