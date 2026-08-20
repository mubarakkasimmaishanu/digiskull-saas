@extends('layouts.master')
@section('page_title', 'Academic Review: ' . $my_class->name . ' ' . $section->name)
@section('content')

<div class="card shadow-sm">
    <div class="card-header bg-white header-elements-inline py-3 border-bottom">
        <div>
            <h5 class="card-title font-weight-bold mb-1 text-primary">
                <i class="icon-books mr-2"></i> Class Academic Review: {{ $my_class->name }} (Section {{ $section->name }})
            </h5>
            <div class="text-muted small">
                Class Teacher Workspace &bull; Academic Session: <span class="badge badge-secondary">{{ $year }}</span>
                @if($selected_exam)
                    &bull; Exam: <span class="badge badge-primary">{{ $selected_exam->name }}</span>
                @endif
            </div>
        </div>
        <div class="header-elements d-flex align-items-center">
            @if($exams->count() > 1)
                <div class="btn-group mr-2">
                    <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle font-weight-semibold" data-toggle="dropdown">
                        <i class="icon-calendar mr-1"></i> Switch Exam ({{ $selected_exam ? $selected_exam->name : 'Select' }})
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        @foreach($exams as $ex)
                            <a href="{{ route('teacher.academic_review', [$section->id, $ex->id]) }}" class="dropdown-item {{ $selected_exam && $ex->id == $selected_exam->id ? 'active' : '' }}">
                                {{ $ex->name }} ({{ $ex->year }})
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <a href="{{ route('teacher.class_detail', $section->id) }}" class="btn btn-sm btn-light border">
                <i class="icon-arrow-left52 mr-1"></i> Class Details
            </a>
        </div>
    </div>

    {{-- Teacher Responsibilities Notice --}}
    <div class="card-body py-2 border-bottom bg-light">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center my-1">
                @if($can_edit_subject_marks)
                    <span class="badge badge-info px-2 py-1 mr-2"><i class="icon-unlocked mr-1"></i> Edit Permitted</span>
                    <span class="small text-muted font-weight-semibold">You have authorized permission to edit subject marks for this class section.</span>
                @else
                    <span class="badge badge-secondary px-2 py-1 mr-2"><i class="icon-lock mr-1"></i> Read-Only Review</span>
                    <span class="small text-muted font-weight-semibold">Subject teachers manage individual subject scores. Class teachers have read-only review rights to prevent data conflicts.</span>
                @endif
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered mb-0 text-nowrap">
            <thead class="bg-light text-uppercase font-size-xs text-muted">
                <tr>
                    <th style="width: 40px;" class="text-center">#</th>
                    <th>Adm No</th>
                    <th>Student Name</th>
                    @foreach($subjects as $sub)
                        <th class="text-center" title="{{ $sub->name }}">
                            {{ $sub->slug ?: $sub->name }}
                        </th>
                    @endforeach
                    <th class="text-center bg-primary-100 text-primary font-weight-bold">Total</th>
                    <th class="text-center bg-primary-100 text-primary font-weight-bold">Average</th>
                    <th class="text-center font-weight-bold">Sec Pos</th>
                    <th class="text-center font-weight-bold">Class Pos</th>
                    <th class="text-center" style="width: 80px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $idx => $st)
                    @php
                        $stMarks = $marks->where('student_id', $st->user_id);
                        $exr = $exam_records->get($st->user_id);
                    @endphp
                    <tr>
                        <td class="text-center text-muted font-weight-semibold">{{ $idx + 1 }}</td>
                        <td class="font-weight-semibold text-primary">
                            <span class="badge badge-light border">{{ $st->adm_no ?: ($st->user->username ?? '—') }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ Usr::getTenantAwarePhoto($st->user->photo) }}" class="rounded-circle mr-2 border" width="28" height="28" alt="">
                                <span class="font-weight-bold text-dark">{{ $st->user->name }}</span>
                            </div>
                        </td>

                        {{-- Inherited Class Subjects --}}
                        @foreach($subjects as $sub)
                            @php
                                $m = $stMarks->where('subject_id', $sub->id)->first();
                                $tot = $m ? $m->cum : null;
                            @endphp
                            <td class="text-center">
                                @if($tot !== null)
                                    <span class="font-weight-semibold text-dark">{{ $tot }}</span>
                                    <span class="badge badge-light border text-muted ml-1" style="font-size: 10px;">
                                        {{ $m->grade ? $m->grade->name : (new \App\Repositories\AssessmentScoreRepo())->getGradeName($tot, $my_class->class_type_id) }}
                                    </span>
                                @else
                                    <span class="text-muted font-italic">—</span>
                                @endif
                            </td>
                        @endforeach

                        {{-- Cumulative Total --}}
                        <td class="text-center font-weight-bold font-size-base bg-primary-50 text-primary">
                            {{ $exr && $exr->total !== null ? $exr->total : ($stMarks->sum('cum') ?: '—') }}
                        </td>

                        {{-- Average --}}
                        <td class="text-center font-weight-bold bg-primary-50 text-primary">
                            {{ $exr && $exr->ave !== null ? $exr->ave . '%' : ($stMarks->count() > 0 ? round($stMarks->sum('cum') / $stMarks->count(), 1) . '%' : '—') }}
                        </td>

                        {{-- Section Position --}}
                        <td class="text-center font-weight-semibold">
                            {{ $exr && $exr->pos ? Mk::getSuffix($exr->pos) : '—' }}
                        </td>

                        {{-- Class Position --}}
                        <td class="text-center font-weight-semibold">
                            {{ $exr && $exr->class_pos ? Mk::getSuffix($exr->class_pos) : '—' }}
                        </td>

                        {{-- Action --}}
                        <td class="text-center">
                            @if($selected_exam)
                                <a href="{{ route('exam_officer.report_card', [$st->user_id, $selected_exam->id, $year]) }}" class="btn btn-xs btn-outline-primary" title="View Report Card" target="_blank">
                                    <i class="icon-file-text2"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 7 + $subjects->count() }}" class="text-center text-muted py-4">
                            No students registered in {{ $my_class->name }} {{ $section->name }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
