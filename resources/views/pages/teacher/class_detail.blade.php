@extends('layouts.master')

@section('page_title', 'Class Roster: ' . ($my_class->name ?? '') . ' - Section ' . ($section->name ?? ''))

@section('content')

{{-- Class Header Card --}}
<div class="card">
    <div class="card-header header-elements-inline">
        <div>
            <h5 class="card-title font-weight-bold mb-0">
                <i class="material-symbols-rounded align-middle mr-1 text-primary">meeting_room</i>
                {{ $my_class->name }} — Section {{ $section->name }}
            </h5>
            <span class="text-muted font-size-sm">
                Class Teacher: <strong>{{ $section->teacher->name ?? 'Not Assigned' }}</strong> |
                Active Students: <strong>{{ $students->count() }}</strong> |
                Configured Subjects: <strong>{{ $inherited_subjects->count() }}</strong>
            </span>
        </div>
        <div class="header-elements">
            <a href="{{ route('teacher.academic_review', $section->id) }}" class="btn btn-sm btn-primary mr-2 shadow-sm font-weight-semibold">
                <i class="icon-books mr-1"></i> Academic Review
            </a>
            <a href="{{ route('attendance.show', [$section->id, $today]) }}" class="btn btn-sm btn-success mr-2 shadow-sm font-weight-semibold">
                <i class="icon-check mr-1"></i> Mark Daily Attendance
            </a>
            <a href="{{ route('teacher.my_class') }}" class="btn btn-sm btn-outline-primary mr-2">
                <i class="icon-arrow-left52 mr-1"></i> Back to My Classes
            </a>
            {!! Qs::getPanelOptions() !!}
        </div>
    </div>

    <div class="card-body pb-0">
        <ul class="nav nav-tabs nav-tabs-highlight mb-0">
            <li class="nav-item">
                <a href="#students-tab" class="nav-link active font-weight-semibold" data-toggle="tab">
                    <i class="material-symbols-rounded mr-1 font-size-sm">groups</i> Enrolled Students ({{ $students->count() }})
                </a>
            </li>
            <li class="nav-item">
                <a href="#subjects-tab" class="nav-link font-weight-semibold" data-toggle="tab">
                    <i class="material-symbols-rounded mr-1 font-size-sm">menu_book</i> Inherited Subjects ({{ $inherited_subjects->count() }})
                </a>
            </li>
            <li class="nav-item">
                <a href="#attendance-tab" class="nav-link font-weight-semibold" data-toggle="tab">
                    <i class="material-symbols-rounded mr-1 font-size-sm">fact_check</i> Attendance Overview
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('teacher.academic_review', $section->id) }}" class="nav-link font-weight-semibold text-primary">
                    <i class="icon-books mr-1 font-size-sm"></i> Academic Review &amp; Scores &rarr;
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="tab-content">
    {{-- TAB 1: Enrolled Students Roster --}}
    <div class="tab-pane fade show active" id="students-tab">
        <div class="card">
            <div class="card-header header-elements-inline bg-light py-2">
                <h6 class="card-title font-weight-semibold mb-0">Registered Student Records</h6>
                <span class="text-muted font-size-sm">Students automatically belong to this class section</span>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover datatable-button-html5-columns">
                        <thead class="bg-slate-800 text-white">
                            <tr>
                                <th style="width: 50px;">S/N</th>
                                <th style="width: 60px;">Photo</th>
                                <th>Adm No</th>
                                <th>Full Name</th>
                                <th>Gender</th>
                                <th>Date of Birth</th>
                                <th>Guardian / Parent</th>
                                <th>Guardian Phone</th>
                                <th class="text-center" style="width: 100px;">Action</th>
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
                                    <td>{{ $st->user->dob ? \Carbon\Carbon::parse($st->user->dob)->format('M d, Y') : '-' }}</td>
                                    <td>{{ $st->my_parent->name ?? 'N/A' }}</td>
                                    <td>{{ $st->my_parent->phone ?? ($st->my_parent->phone2 ?? ($st->user->phone ?? 'N/A')) }}</td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="{{ route('students.show', Qs::hash($st->id)) }}" class="btn btn-sm btn-outline-primary" title="View Full Profile">
                                                <i class="material-symbols-rounded font-size-sm">person</i>
                                            </a>
                                            <a href="{{ route('attendance.student_report', $st->id) }}" class="btn btn-sm btn-outline-info ml-1" title="Attendance Stats">
                                                <i class="material-symbols-rounded font-size-sm">fact_check</i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-3">No students registered in this section yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 2: Inherited Class Subjects (Read-only as per spec) --}}
    <div class="tab-pane fade" id="subjects-tab">
        <div class="card">
            <div class="card-header bg-light py-2">
                <h6 class="card-title font-weight-semibold mb-0">Class Subjects Configuration</h6>
            </div>

            <div class="card-body">
                <div class="alert alert-secondary border-0 d-flex align-items-start mb-4">
                    <i class="material-symbols-rounded text-primary font-size-24 mr-2">lock</i>
                    <div>
                        <strong class="text-dark">Automatic Subject Inheritance:</strong>
                        <p class="mb-0 text-muted font-size-sm">
                            Subjects are configured at the <strong>Class Level ({{ $my_class->name }})</strong> by the school administration.
                            All {{ $students->count() }} students enrolled in {{ $my_class->name }} - {{ $section->name }} automatically inherit these subjects without manual assignment.
                            <br><em>(Subject configuration is read-only for Class Teachers)</em>
                        </p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="bg-slate-800 text-white">
                            <tr>
                                <th style="width: 50px;">S/N</th>
                                <th>Subject Name</th>
                                <th>Short Code</th>
                                <th>Subject Type</th>
                                <th>Assigned Subject Teacher(s)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inherited_subjects as $sub)
                                @php
                                    $sub_recs = $sub->record ? $sub->record->where('section_id', $section->id) : collect([]);
                                    if ($sub_recs->isEmpty() && $sub->record) {
                                        $sub_recs = $sub->record->whereNull('section_id');
                                    }
                                @endphp
                                <tr>
                                    <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                                    <td class="font-weight-bold">{{ $sub->name }}</td>
                                    <td><span class="badge badge-secondary font-weight-semibold">{{ $sub->slug }}</span></td>
                                    <td>
                                        @if($sub->core)
                                            <span class="badge badge-primary">Core Subject</span>
                                        @else
                                            <span class="badge badge-secondary">Elective</span>
                                        @endif
                                    </td>
                                    <td>
                                        @forelse($sub_recs as $rec)
                                            <span class="badge badge-secondary border p-1 mr-1">
                                                <i class="material-symbols-rounded font-size-xs align-middle mr-1">person</i>
                                                {{ $rec->teacher->name ?? 'Unassigned' }}
                                            </span>
                                        @empty
                                            <span class="text-muted font-italic font-size-sm">No teacher assigned</span>
                                        @endforelse
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No subjects configured for {{ $my_class->name }} yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 3: Attendance Summary --}}
    <div class="tab-pane fade" id="attendance-tab">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <h6 class="card-title font-weight-semibold mb-0">Class Attendance Summary</h6>
                <a href="{{ route('attendance.show', [$section->id, $today]) }}" class="btn btn-sm btn-primary">
                    <i class="material-symbols-rounded font-size-sm mr-1">edit_calendar</i> Go to Daily Register
                </a>
            </div>

            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="card card-body bg-light border text-center">
                            <h3 class="font-weight-bold mb-0 text-primary">{{ $total_school_days }}</h3>
                            <span class="text-muted font-size-sm">Total Days School Opened</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-body bg-light border text-center">
                            <h3 class="font-weight-bold mb-0 text-success">{{ $students->count() }}</h3>
                            <span class="text-muted font-size-sm">Enrolled Students</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-body bg-light border text-center">
                            <h3 class="font-weight-bold mb-0 text-info">
                                {{ $attendance_today->isNotEmpty() ? 'Marked' : 'Pending' }}
                            </h3>
                            <span class="text-muted font-size-sm">Today's Register ({{ \Carbon\Carbon::parse($today)->format('M d') }})</span>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="bg-slate-800 text-white">
                            <tr>
                                <th style="width: 50px;">S/N</th>
                                <th>Student Name</th>
                                <th class="text-center">Days Opened</th>
                                <th class="text-center text-success">Present</th>
                                <th class="text-center text-danger">Absent</th>
                                <th class="text-center" style="width: 180px;">Attendance Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendance_summary['students_summary'] as $sum)
                                @php
                                    $pct = $sum['percentage'];
                                    $badge = $pct >= 75 ? 'badge-success' : ($pct >= 50 ? 'badge-warning' : 'badge-danger');
                                @endphp
                                <tr>
                                    <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                                    <td class="font-weight-semibold">{{ $sum['student']->user->name ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $sum['total_days'] }}</td>
                                    <td class="text-center font-weight-bold text-success">{{ $sum['present_days'] }}</td>
                                    <td class="text-center font-weight-bold text-danger">{{ $sum['absent_days'] }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $badge }} font-weight-bold">{{ $pct }}%</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No attendance data recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
