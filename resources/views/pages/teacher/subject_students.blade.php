@extends('layouts.master')

@section('page_title', ($subject->name ?? 'Subject') . ' - ' . ($my_class->name ?? '') . ' ' . ($section->name ?? ''))

@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <div>
            <h5 class="card-title font-weight-bold mb-0">
                <i class="material-symbols-rounded align-middle mr-1 text-primary">book</i>
                {{ $subject->name }} — {{ $my_class->name }} (Section {{ $section->name }})
            </h5>
            <span class="text-muted font-size-sm">
                Subject Teacher Roster | Academic Session: <strong>{{ $session }}</strong> | Total Students: <strong>{{ $students->count() }}</strong>
            </span>
        </div>
        <div class="header-elements">
            <a href="{{ route('teacher.my_subjects') }}" class="btn btn-sm btn-outline-primary mr-2">
                <i class="material-symbols-rounded mr-1 font-size-sm">arrow_back</i> Back to My Subjects
            </a>
            {!! Qs::getPanelOptions() !!}
        </div>
    </div>

    <div class="card-body">
        <div class="alert alert-info border-0 alert-dismissible mb-4">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            <div class="d-flex align-items-start">
                <i class="material-symbols-rounded mr-2 font-size-24">info</i>
                <div>
                    <strong>Class-Based Membership:</strong> All {{ $students->count() }} students shown below are automatically populated from their enrollment in <strong>{{ $my_class->name }} - {{ $section->name }}</strong>.
                    As students are admitted or transferred into this class, they automatically inherit this subject without any manual selection needed.
                </div>
            </div>
        </div>

        {{-- Score Entry Interface (Phase 2) --}}
        <div class="card border border-primary bg-primary-50 mb-4 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="d-flex align-items-center mb-2 mb-md-0">
                        <i class="icon-file-text2 text-primary icon-2x mr-3"></i>
                        <div>
                            <h6 class="font-weight-bold mb-0 text-primary">Academic Assessment &amp; Score Entry (Phase 2)</h6>
                            <span class="text-muted font-size-sm">Enter CA 1, CA 2, and Examination scores for enrolled students in {{ $my_class->name }} {{ $section->name }}.</span>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('teacher.scores.index', [$subject->id, $section->id]) }}" class="btn btn-primary font-weight-semibold shadow-sm">
                            <i class="icon-pencil7 mr-1"></i> Enter / Edit Scores (CA1, CA2, Exam)
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Students List Table --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover datatable-button-html5-columns">
                <thead class="bg-slate-800 text-white">
                    <tr>
                        <th style="width: 50px;">S/N</th>
                        <th style="width: 60px;">Photo</th>
                        <th>Adm No</th>
                        <th>Student Name</th>
                        <th>Gender</th>
                        <th>Class &amp; Section</th>
                        <th>Academic Session</th>
                        <th class="text-center" style="width: 150px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $st)
                        <tr>
                            <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                            <td class="text-center">
                                <img src="{{ Usr::getTenantAwarePhoto($st->user->photo) }}" width="36" height="36" class="rounded-circle shadow-sm" alt="photo">
                            </td>
                            <td class="font-weight-semibold text-primary">
                                {{ $st->adm_no ?: ($st->user->username ?? '-') }}
                            </td>
                            <td class="font-weight-bold">
                                {{ $st->user->name ?? 'N/A' }}
                            </td>
                            <td>{{ $st->user->gender ?? 'N/A' }}</td>
                            <td>{{ $my_class->name }} - {{ $section->name }}</td>
                            <td><span class="badge badge-light font-weight-semibold">{{ $st->session ?? $session }}</span></td>
                            <td class="text-center">
                                <span class="badge badge-success px-2 py-1">
                                    <i class="material-symbols-rounded font-size-xs mr-1 align-middle">check_circle</i> Enrolled
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">No students found in {{ $my_class->name }} - {{ $section->name }}.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
