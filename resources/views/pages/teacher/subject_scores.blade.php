@extends('layouts.master')
@section('page_title', 'Score Entry: ' . $subject->name . ' - ' . $my_class->name . ' ' . $section->name)
@section('content')

<div class="card shadow-sm">
    <div class="card-header bg-white header-elements-inline py-3 border-bottom">
        <div>
            <h5 class="card-title font-weight-bold mb-1 text-primary">
                <i class="icon-file-text2 mr-2"></i> Score Entry: {{ $subject->name }}
            </h5>
            <div class="text-muted small">
                <span class="font-weight-semibold">{{ $my_class->name }} ({{ $section->name }})</span> &bull; 
                Academic Session: <span class="badge badge-secondary">{{ $year }}</span> &bull; 
                Exam: <span class="badge badge-primary">{{ $selected_exam->name }}</span>
            </div>
        </div>
        <div class="header-elements d-flex align-items-center">
            @if($exams->count() > 1)
                <div class="btn-group mr-2">
                    <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle font-weight-semibold" data-toggle="dropdown">
                        <i class="icon-calendar mr-1"></i> Switch Exam ({{ $selected_exam->name }})
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        @foreach($exams as $ex)
                            <a href="{{ route('teacher.scores.index', [$subject->id, $section->id, $ex->id]) }}" class="dropdown-item {{ $ex->id == $selected_exam->id ? 'active' : '' }}">
                                {{ $ex->name }} ({{ $ex->year }})
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <a href="{{ route('teacher.subject_students', [$subject->id, $section->id]) }}" class="btn btn-sm btn-light border mr-2">
                <i class="icon-arrow-left52 mr-1"></i> Class Roster
            </a>
        </div>
    </div>

    {{-- Workflow Status Banner --}}
    <div class="card-body py-2 border-bottom bg-light">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center my-1">
                <span class="text-muted mr-2 font-weight-semibold">Submission Status:</span>
                @if($submission->isApproved())
                    <span class="badge badge-success px-3 py-1 font-weight-semibold" style="font-size: 13px;">
                        <i class="icon-checkmark-circle mr-1"></i> Approved by Exam Officer (Locked)
                    </span>
                @elseif($submission->isLocked())
                    <span class="badge badge-dark px-3 py-1 font-weight-semibold" style="font-size: 13px;">
                        <i class="icon-lock mr-1"></i> Locked
                    </span>
                @elseif($submission->isSubmitted())
                    <span class="badge badge-info px-3 py-1 font-weight-semibold" style="font-size: 13px;">
                        <i class="icon-paperplane mr-1"></i> Submitted — Pending Review
                    </span>
                @elseif($submission->isRejected())
                    <span class="badge badge-danger px-3 py-1 font-weight-semibold" style="font-size: 13px;">
                        <i class="icon-warning2 mr-1"></i> Returned for Correction
                    </span>
                @else
                    <span class="badge badge-warning px-3 py-1 font-weight-semibold text-dark" style="font-size: 13px;">
                        <i class="icon-pencil5 mr-1"></i> Draft (Editable)
                    </span>
                @endif
            </div>

            @if($submission->isRejected() && $submission->rejection_reason)
                <div class="alert alert-danger py-1 px-3 mb-0 my-1 small font-weight-semibold">
                    <i class="icon-info22 mr-1"></i> Reason: {{ $submission->rejection_reason }}
                </div>
            @endif

            <div class="text-muted small my-1">
                @if($submission->submitted_at)
                    Submitted: <strong>{{ $submission->submitted_at->format('M d, Y h:i A') }}</strong>
                @endif
                @if($submission->reviewed_at)
                    &bull; Reviewed: <strong>{{ $submission->reviewed_at->format('M d, Y h:i A') }}</strong>
                @endif
            </div>
        </div>
    </div>

    @php
        $isEditable = $submission->isEditableByTeacher() || Qs::userIsTeamSA();
    @endphp

    <form method="POST" id="scoreEntryForm">
        @csrf
        <input type="hidden" name="exam_id" value="{{ $selected_exam->id }}">

        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered mb-0">
                <thead class="bg-light text-uppercase font-size-xs text-muted">
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th style="width: 120px;">Adm No</th>
                        <th>Student Name</th>
                        <th style="width: 90px;" class="text-center">Gender</th>
                        <th style="width: 130px;" class="text-center bg-primary-100 text-primary font-weight-bold">
                            CA 1
                        </th>
                        <th style="width: 130px;" class="text-center bg-primary-100 text-primary font-weight-bold">
                            CA 2
                        </th>
                        <th style="width: 140px;" class="text-center bg-primary-100 text-primary font-weight-bold">
                            Examination
                        </th>
                        <th style="width: 110px;" class="text-center font-weight-bold">Total</th>
                        <th style="width: 90px;" class="text-center font-weight-bold">Grade</th>
                        <th style="width: 90px;" class="text-center font-weight-bold">Pos</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $idx => $row)
                        @php
                            $stUser = $row['user'];
                            $sr = $row['student_record'];
                        @endphp
                        <tr id="row-{{ $stUser->id }}">
                            <td class="text-center text-muted font-weight-semibold">{{ $idx + 1 }}</td>
                            <td class="font-weight-semibold text-primary">
                                <span class="badge badge-light border">{{ $sr->adm_no ?: ($stUser->username ?: '—') }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ Usr::getTenantAwarePhoto($stUser->photo) }}" class="rounded-circle mr-2 border" width="32" height="32" alt="">
                                    <span class="font-weight-bold text-dark">{{ $stUser->name }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $stUser->gender === 'Female' ? 'badge-pink' : 'badge-light border' }}">
                                    {{ $stUser->gender ?: '—' }}
                                </span>
                            </td>

                            {{-- CA 1 Input --}}
                            <td class="p-1 text-center bg-primary-50">
                                <input type="number" 
                                       step="1" 
                                       min="0" 
                                       max="100" 
                                       name="scores[{{ $stUser->id }}][ca1]" 
                                       value="{{ $row['ca1'] !== null ? $row['ca1'] : '' }}" 
                                       class="form-control text-center font-weight-semibold score-input ca1-input" 
                                       data-student="{{ $stUser->id }}" 
                                       placeholder="—"
                                       {{ !$isEditable ? 'readonly disabled' : '' }}>
                            </td>

                            {{-- CA 2 Input --}}
                            <td class="p-1 text-center bg-primary-50">
                                <input type="number" 
                                       step="1" 
                                       min="0" 
                                       max="100" 
                                       name="scores[{{ $stUser->id }}][ca2]" 
                                       value="{{ $row['ca2'] !== null ? $row['ca2'] : '' }}" 
                                       class="form-control text-center font-weight-semibold score-input ca2-input" 
                                       data-student="{{ $stUser->id }}" 
                                       placeholder="—"
                                       {{ !$isEditable ? 'readonly disabled' : '' }}>
                            </td>

                            {{-- Examination Input --}}
                            <td class="p-1 text-center bg-primary-50">
                                <input type="number" 
                                       step="1" 
                                       min="0" 
                                       max="100" 
                                       name="scores[{{ $stUser->id }}][exm]" 
                                       value="{{ $row['exm'] !== null ? $row['exm'] : '' }}" 
                                       class="form-control text-center font-weight-semibold score-input exm-input" 
                                       data-student="{{ $stUser->id }}" 
                                       placeholder="—"
                                       {{ !$isEditable ? 'readonly disabled' : '' }}>
                            </td>

                            {{-- Total (Calculated Live) --}}
                            <td class="text-center font-weight-bold font-size-base total-cell" id="total-{{ $stUser->id }}">
                                {{ $row['total'] !== null ? $row['total'] : '—' }}
                            </td>

                            {{-- Grade --}}
                            <td class="text-center font-weight-bold grade-cell" id="grade-{{ $stUser->id }}">
                                @if($row['grade'])
                                    <span class="badge badge-primary">{{ $row['grade']->name }}</span>
                                @elseif($row['total'] !== null)
                                    <span class="badge badge-secondary">{{ (new \App\Repositories\AssessmentScoreRepo())->getGradeName($row['total'], $my_class->class_type_id) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Position --}}
                            <td class="text-center font-weight-semibold text-muted">
                                {{ $row['sub_pos'] ? Mk::getSuffix($row['sub_pos']) : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="icon-users4 icon-2x mb-2 text-muted"></i>
                                <p class="mb-0">No active students registered in this section for academic session {{ $year }}.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Bottom Actions --}}
        <div class="card-footer bg-white border-top d-flex align-items-center justify-content-between flex-wrap py-3">
            <div class="text-muted small">
                <i class="icon-info22 mr-1 text-primary"></i>
                <strong>Total = CA 1 + CA 2 + Examination</strong>. Position & grading calculate automatically upon saving.
            </div>

            @if($isEditable)
                <div class="d-flex align-items-center">
                    <button type="submit" formaction="{{ route('teacher.scores.save', [$subject->id, $section->id]) }}" class="btn btn-outline-primary mr-2 font-weight-semibold shadow-sm">
                        <i class="icon-floppy-disk mr-1"></i> Save Draft
                    </button>
                    <button type="submit" formaction="{{ route('teacher.scores.submit', [$subject->id, $section->id]) }}" class="btn btn-success font-weight-semibold shadow-sm" onclick="return confirm('Are you sure you want to submit these scores to the Examination Officer? Once submitted, they will be reviewed and locked.')">
                        <i class="icon-paperplane mr-1"></i> Submit to Exam Officer
                    </button>
                </div>
            @else
                <div class="text-muted small font-italic font-weight-semibold text-danger">
                    <i class="icon-lock mr-1"></i> Scores cannot be edited because this score package is {{ $submission->status }}.
                </div>
            @endif
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function calculateRow(studentId) {
        var row = document.getElementById('row-' + studentId);
        if (!row) return;

        var ca1Val = row.querySelector('.ca1-input').value.trim();
        var ca2Val = row.querySelector('.ca2-input').value.trim();
        var exmVal = row.querySelector('.exm-input').value.trim();

        var ca1 = ca1Val !== '' ? parseFloat(ca1Val) : null;
        var ca2 = ca2Val !== '' ? parseFloat(ca2Val) : null;
        var exm = exmVal !== '' ? parseFloat(exmVal) : null;

        var totalCell = document.getElementById('total-' + studentId);
        var gradeCell = document.getElementById('grade-' + studentId);

        if (ca1 !== null || ca2 !== null || exm !== null) {
            var total = (ca1 || 0) + (ca2 || 0) + (exm || 0);
            totalCell.textContent = total;

            // Simple client-side grade estimation
            var grade = 'F';
            if (total >= 70) grade = 'A';
            else if (total >= 60) grade = 'B';
            else if (total >= 50) grade = 'C';
            else if (total >= 45) grade = 'D';
            else if (total >= 40) grade = 'E';

            gradeCell.innerHTML = '<span class="badge badge-primary">' + grade + '</span>';
        } else {
            totalCell.textContent = '—';
            gradeCell.innerHTML = '<span class="text-muted">—</span>';
        }
    }

    document.querySelectorAll('.score-input').forEach(function(input) {
        input.addEventListener('input', function() {
            var studentId = this.getAttribute('data-student');
            calculateRow(studentId);
        });
    });
});
</script>

@endsection
