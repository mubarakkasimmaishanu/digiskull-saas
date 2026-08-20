@extends('layouts.master')
@section('page_title', 'Results Generation & Class Rankings')
@section('content')

<div class="card shadow-sm">
    <div class="card-header bg-white header-elements-inline py-3 border-bottom">
        <div>
            <h5 class="card-title font-weight-bold mb-1 text-primary">
                <i class="icon-calculator mr-2"></i> Results &amp; Positions Generation
            </h5>
            <span class="text-muted small">
                Compute overall student results, subject positions, cumulative totals, averages, and class rankings.
            </span>
        </div>
        <div class="header-elements">
            <a href="{{ route('exam_officer.dashboard') }}" class="btn btn-sm btn-light border mr-2">
                <i class="icon-arrow-left52 mr-1"></i> Dashboard
            </a>
        </div>
    </div>

    {{-- Class & Exam Selector --}}
    <div class="card-body bg-light border-bottom py-3">
        <form method="GET" action="{{ route('exam_officer.results_generation') }}">
            <div class="row align-items-end">
                <div class="col-md-3 mb-2">
                    <label class="font-weight-semibold font-size-xs text-muted text-uppercase mb-1">Examination</label>
                    <select name="exam_id" class="form-control form-control-sm" required>
                        @foreach($exams as $ex)
                            <option value="{{ $ex->id }}" {{ $selected_exam == $ex->id ? 'selected' : '' }}>
                                {{ $ex->name }} ({{ $ex->year }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-2">
                    <label class="font-weight-semibold font-size-xs text-muted text-uppercase mb-1">Class</label>
                    <select name="class_id" class="form-control form-control-sm" required>
                        <option value="">Select Class</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $selected_class == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-2">
                    <label class="font-weight-semibold font-size-xs text-muted text-uppercase mb-1">Section (Optional)</label>
                    <select name="section_id" class="form-control form-control-sm">
                        <option value="">All Sections</option>
                        @if($selected_class)
                            @php
                                $activeClass = $classes->where('id', $selected_class)->first();
                            @endphp
                            @if($activeClass)
                                @foreach($activeClass->section as $sec)
                                    <option value="{{ $sec->id }}" {{ $selected_section == $sec->id ? 'selected' : '' }}>
                                        {{ $sec->name }}
                                    </option>
                                @endforeach
                            @endif
                        @endif
                    </select>
                </div>

                <div class="col-md-3 mb-2 d-flex">
                    <button type="submit" class="btn btn-sm btn-primary font-weight-semibold mr-2 flex-grow-1">
                        <i class="icon-search4 mr-1"></i> View Results
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if($selected_exam && $selected_class)
        {{-- Results Action Banner --}}
        <div class="card-body py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap">
            <div class="my-1">
                <span class="font-weight-bold text-dark mr-2">Results Status:</span>
                @if(count($exam_records) > 0)
                    <span class="badge badge-success px-3 py-1 font-size-sm">
                        <i class="icon-checkmark2 mr-1"></i> {{ count($exam_records) }} Student Results Computed
                    </span>
                @else
                    <span class="badge badge-warning px-3 py-1 font-size-sm text-dark">
                        <i class="icon-info22 mr-1"></i> Results Not Yet Generated
                    </span>
                @endif
            </div>

            <div class="d-flex align-items-center my-1">
                <form method="POST" action="{{ route('exam_officer.generate_results') }}" class="d-inline mr-2">
                    @csrf
                    <input type="hidden" name="exam_id" value="{{ $selected_exam }}">
                    <input type="hidden" name="class_id" value="{{ $selected_class }}">
                    <input type="hidden" name="section_id" value="{{ $selected_section }}">
                    <button type="submit" class="btn btn-sm btn-success font-weight-semibold shadow-sm" onclick="return confirm('Generate and re-calculate all positions and averages for this class?');">
                        <i class="icon-calculator mr-1"></i> Compute / Update Results
                    </button>
                </form>

                @if(count($exam_records) > 0)
                    <a href="{{ route('exam_officer.bulk_report_cards', ['exam_id' => $selected_exam, 'class_id' => $selected_class, 'section_id' => $selected_section, 'year' => $year]) }}" class="btn btn-sm btn-outline-primary font-weight-semibold" target="_blank">
                        <i class="icon-printer mr-1"></i> Print Batch Report Cards
                    </a>
                @endif
            </div>
        </div>

        {{-- Results Table --}}
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered mb-0">
                <thead class="bg-slate-800 text-white font-size-xs text-uppercase">
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th>Adm No</th>
                        <th>Student Name</th>
                        <th class="text-center">Section</th>
                        <th class="text-center font-weight-bold">Total Marks</th>
                        <th class="text-center font-weight-bold">Average</th>
                        <th class="text-center font-weight-bold">Grade</th>
                        <th class="text-center font-weight-bold">Section Pos</th>
                        <th class="text-center font-weight-bold">Class Pos</th>
                        <th class="text-center" style="width: 120px;">Report Card</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exam_records as $idx => $exr)
                        <tr>
                            <td class="text-center text-muted font-weight-semibold">{{ $idx + 1 }}</td>
                            <td class="font-weight-semibold text-primary">
                                <span class="badge badge-light border">{{ $exr->student->username ?? '—' }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ Usr::getTenantAwarePhoto($exr->student->photo ?? null) }}" class="rounded-circle mr-2 border" width="30" height="30" alt="">
                                    <span class="font-weight-bold text-dark">{{ $exr->student->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-secondary">{{ $exr->section->name ?? '—' }}</span>
                            </td>
                            <td class="text-center font-weight-bold font-size-base text-primary">
                                {{ $exr->total !== null ? $exr->total : '—' }}
                            </td>
                            <td class="text-center font-weight-bold text-primary">
                                {{ $exr->ave !== null ? $exr->ave . '%' : '—' }}
                            </td>
                            <td class="text-center font-weight-bold">
                                @if($exr->grade)
                                    <span class="badge badge-primary">{{ $exr->grade->name }}</span>
                                @elseif($exr->ave !== null)
                                    <span class="badge badge-secondary">{{ (new \App\Repositories\AssessmentScoreRepo())->getGradeName($exr->ave, $exr->my_class->class_type_id ?? null) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center font-weight-semibold">
                                {{ $exr->pos ? Mk::getSuffix($exr->pos) : '—' }}
                            </td>
                            <td class="text-center font-weight-bold text-success">
                                {{ $exr->class_pos ? Mk::getSuffix($exr->class_pos) : '—' }}
                            </td>
                            <td class="text-center">
                                <a href="{{ route('exam_officer.report_card', [$exr->student_id, $selected_exam, $year]) }}" class="btn btn-xs btn-outline-primary" target="_blank">
                                    <i class="icon-file-text2 mr-1"></i> Report Card
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                No computed results found for this class and exam. Click <strong>Compute / Update Results</strong> above to generate positions and rankings.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div class="card-body text-center text-muted py-5">
            <i class="icon-calculator icon-2x mb-2 text-muted"></i>
            <p class="mb-0">Please select an Examination and Class to view or compute results.</p>
        </div>
    @endif
</div>

@endsection
