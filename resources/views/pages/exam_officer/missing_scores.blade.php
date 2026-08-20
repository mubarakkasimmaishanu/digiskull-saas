@extends('layouts.master')
@section('page_title', 'Missing Scores Audit')
@section('content')

<div class="card shadow-sm">
    <div class="card-header bg-white header-elements-inline py-3 border-bottom">
        <div>
            <h5 class="card-title font-weight-bold mb-1 text-danger">
                <i class="icon-shield-notice mr-2"></i> Missing &amp; Incomplete Scores Auditor
            </h5>
            <span class="text-muted small">
                Verify score completeness across all classes and subjects before generating final term results.
            </span>
        </div>
        <div class="header-elements">
            <a href="{{ route('exam_officer.dashboard') }}" class="btn btn-sm btn-light border mr-2">
                <i class="icon-arrow-left52 mr-1"></i> Dashboard
            </a>
            @if($summary && $summary['is_ready_for_results'])
                <a href="{{ route('exam_officer.results_generation', ['exam_id' => $selected_exam]) }}" class="btn btn-sm btn-success font-weight-semibold shadow-sm">
                    <i class="icon-calculator mr-1"></i> Ready to Generate Results &rarr;
                </a>
            @endif
        </div>
    </div>

    {{-- Filter by Exam & Class --}}
    <div class="card-body bg-light border-bottom py-3">
        <form method="GET" action="{{ route('exam_officer.missing_scores') }}">
            <div class="row align-items-end">
                <div class="col-md-4 mb-2">
                    <label class="font-weight-semibold font-size-xs text-muted text-uppercase mb-1">Examination</label>
                    <select name="exam_id" class="form-control form-control-sm">
                        @foreach($exams as $ex)
                            <option value="{{ $ex->id }}" {{ $selected_exam == $ex->id ? 'selected' : '' }}>
                                {{ $ex->name }} ({{ $ex->year }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 mb-2">
                    <label class="font-weight-semibold font-size-xs text-muted text-uppercase mb-1">Class (Optional Filter)</label>
                    <select name="class_id" class="form-control form-control-sm">
                        <option value="">All Classes</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $selected_class == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 mb-2 d-flex">
                    <button type="submit" class="btn btn-sm btn-primary font-weight-semibold mr-2 flex-grow-1">
                        <i class="icon-search4 mr-1"></i> Audit Scores
                    </button>
                    <a href="{{ route('exam_officer.missing_scores') }}" class="btn btn-sm btn-light border">Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Summary Statistics --}}
    @if($summary)
        <div class="card-body py-3 border-bottom">
            <div class="row">
                <div class="col-md-3">
                    <div class="p-2 border rounded bg-white text-center">
                        <div class="font-size-xs text-muted font-weight-semibold text-uppercase">Total Packages</div>
                        <h4 class="font-weight-bold mb-0 text-dark">{{ $summary['total_packages'] }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2 border rounded bg-white text-center">
                        <div class="font-size-xs text-muted font-weight-semibold text-uppercase">Approved Packages</div>
                        <h4 class="font-weight-bold mb-0 text-success">{{ $summary['approved'] + $summary['locked'] }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2 border rounded bg-white text-center">
                        <div class="font-size-xs text-muted font-weight-semibold text-uppercase">Unsubmitted / Drafts</div>
                        <h4 class="font-weight-bold mb-0 text-warning">{{ $summary['draft_or_missing'] }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2 border rounded bg-white text-center">
                        <div class="font-size-xs text-muted font-weight-semibold text-uppercase">Missing Student Scores</div>
                        <h4 class="font-weight-bold mb-0 text-danger">{{ $summary['incomplete_students'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Audit Breakdown --}}
    <div class="card-body">
        @forelse($audit as $classItem)
            <div class="mb-4">
                <h6 class="font-weight-bold text-primary mb-2 border-bottom pb-1">
                    <i class="icon-graduation2 mr-1"></i> Class: {{ $classItem['class']->name }}
                </h6>

                @foreach($classItem['sections'] as $secItem)
                    <div class="card border mb-3 shadow-none">
                        <div class="card-header bg-light py-2 header-elements-inline">
                            <span class="font-weight-bold text-dark">Section {{ $secItem['section']->name }}</span>
                            <span class="badge badge-secondary">{{ count($secItem['subjects']) }} Inherited Subjects</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-bordered mb-0">
                                <thead class="bg-slate-800 text-white font-size-xs">
                                    <tr>
                                        <th>Subject</th>
                                        <th>Submission Status</th>
                                        <th>Missing Student Scores Breakdown</th>
                                        <th class="text-center" style="width: 120px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($secItem['subjects'] as $subItem)
                                        @php
                                            $hasMissing = !empty($subItem['missing_scores']);
                                        @endphp
                                        <tr class="{{ $hasMissing ? 'bg-danger-50' : '' }}">
                                            <td class="font-weight-bold">{{ $subItem['subject']->name }}</td>
                                            <td>
                                                @if($subItem['status'] === 'approved')
                                                    <span class="badge badge-success"><i class="icon-check mr-1"></i> Approved</span>
                                                @elseif($subItem['status'] === 'locked')
                                                    <span class="badge badge-dark"><i class="icon-lock mr-1"></i> Locked</span>
                                                @elseif($subItem['status'] === 'submitted')
                                                    <span class="badge badge-info"><i class="icon-paperplane mr-1"></i> Submitted</span>
                                                @elseif($subItem['status'] === 'rejected')
                                                    <span class="badge badge-danger"><i class="icon-warning mr-1"></i> Returned</span>
                                                @else
                                                    <span class="badge badge-warning"><i class="icon-pencil mr-1"></i> Not Submitted</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($hasMissing)
                                                    <div class="text-danger small">
                                                        <strong>{{ count($subItem['missing_scores']) }} student(s) missing marks:</strong>
                                                        <ul class="mb-0 pl-3">
                                                            @foreach($subItem['missing_scores'] as $m)
                                                                <li>
                                                                    {{ $m['student']->name ?? 'N/A' }} ({{ $m['adm_no'] }}): Missing 
                                                                    <span class="font-weight-bold">{{ implode(', ', $m['missing']) }}</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @else
                                                    <span class="text-success small font-weight-semibold">
                                                        <i class="icon-checkmark2 mr-1"></i> All enrolled student marks recorded
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($subItem['submission'])
                                                    <a href="{{ route('exam_officer.review', $subItem['submission']->id) }}" class="btn btn-xs btn-outline-primary">
                                                        <i class="icon-eye mr-1"></i> Review
                                                    </a>
                                                @else
                                                    <span class="text-muted small">No entry yet</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <div class="text-center text-muted py-5">
                <i class="icon-search4 icon-2x mb-2 text-muted"></i>
                <p>Select an exam above to run the missing scores audit.</p>
            </div>
        @endforelse
    </div>
</div>

@endsection
