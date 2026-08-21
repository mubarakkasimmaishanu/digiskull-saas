@extends('layouts.master')

@section('page_title', 'My Dashboard')

@section('content')

@if(!Qs::currentSessionMatchesCurrentYear() && Qs::userIsSuperAdmin())
@php session()->flash('pop_warning_confirm', __('msg.session_and_year_not_matched')) @endphp
@endif

@if (isset($setup_tracker) && $setup_progress_percent < 100)
<div class="card border-left-info mb-3">
    <div class="card-header header-elements-inline">
        <h6 class="card-title text-info font-weight-bold">School Onboarding & Setup Progress ({{ $setup_progress_percent }}% Complete)</h6>
    </div>
    <div class="card-body">
        <div class="progress mb-3" style="height: 8px;">
            <div class="progress-bar bg-info" style="width: {{ $setup_progress_percent }}%"></div>
        </div>
        <div class="row text-center">
            @foreach ($setup_tracker as $label => $completed)
                <div class="col-md col-6 mb-2">
                    <span class="material-symbols-rounded {{ $completed ? 'text-success' : 'text-danger' }} symbol-sm align-middle mr-1">
                        {{ $completed ? 'check_circle' : 'cancel' }}
                    </span>
                    <span class="small text-muted">{{ $label }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

@if (Qs::userIsAdministrative() or Qs::userIsLibrarian())

@php
$staff = $users->where('user_type', '<>', 'student')->where('user_type', '<>', 'parent')
@endphp

<div class="card-columns">
    <!-- Active Students -->
    <div class="card card-body bg-purple-400 has-bg-image">
        <div class="media">
            <div class="media-body">
                <div class="row">
                    <div class="col-4">
                        <h3 class="mb-0 counter">{{ $student_recs->where('grad', 0)->count() }}</h3>
                    </div>
                    <div class="col-6">
                        <div class="row">
                            <div class="col-6">
                                M:
                                {{ $student_recs->where('grad', 0)->where('user.gender', 'Male')->count() }}
                            </div>
                            <div class="col-6">
                                F:
                                {{ $student_recs->where('grad', 0)->where('user.gender', 'Female')->count() }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <span class="text-capitalize font-size-xs">Active Students</span>
                    </div>
                </div>
            </div>
            <div class="ml-3 align-self-center">
                <a href="javascript:;" class="material-symbols-rounded symbol-3x opacity-75" data-toggle="modal" data-target="#students-number">groups</a>
            </div>
        </div>
    </div>

    @if (Qs::userIsTeamSA())
    <!-- Graduated students -->
    <div class="card card-body bg-purple-300 has-bg-image">
        <div class="media">
            <div class="media-body">
                <div class="row">
                    <div class="col-4">
                        <h3 class="mb-0 counter">{{ $grad_students->count() }}</h3>
                    </div>
                    <div class="col-6">
                        <div class="row">
                            <div class="col-6">
                                M:
                                {{ $grad_students->where('user.gender', 'Male')->count() }}
                            </div>
                            <div class="col-6">
                                F:
                                {{ $grad_students->where('user.gender', 'Female')->count() }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <span class="text-capitalize font-size-xs">Graduated Students</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overall students -->
    <div class="card card-body bg-purple-400 has-bg-image">
        <div class="media">
            <div class="media-body">
                <div class="row">
                    <div class="col-4">
                        <h3 class="mb-0 counter">{{ $student_recs->count() }}</h3>
                    </div>
                    <div class="col-6">
                        <div class="row">
                            <div class="col-6">
                                M:
                                {{ $student_recs->where('user.gender', 'Male')->count() }}
                            </div>
                            <div class="col-6">
                                F:
                                {{ $student_recs->where('user.gender', 'Female')->count() }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <span class="text-capitalize font-size-xs">Overall Students</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Administrators -->
    <div class="card card-body bg-danger-600 has-bg-image">
        <div class="media">
            <div class="media-body">
                <div class="row">
                    <div class="col-4">
                        <h3 class="mb-0 counter">
                            {{ $staff->where('user_type', 'super_admin')->count() + $staff->where('user_type', 'admin')->count() }}
                        </h3>
                    </div>
                    <div class="col-6">
                        <div class="row">
                            <div class="col-6">
                                M:
                                {{ $staff->where('user_type', 'super_admin')->where('gender', 'Male')->count() + $staff->where('user_type', 'admin')->where('gender', 'Male')->count() }}
                            </div>
                            <div class="col-6">
                                F:
                                {{ $staff->where('user_type', 'super_admin')->where('gender', 'Female')->count() + $staff->where('user_type', 'admin')->where('gender', 'Female')->count() }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <span class="text-capitalize font-size-xs">Total Administrators</span>
                    </div>
                </div>
            </div>
            <div class="ml-3 align-self-center d-none d-md-block">
                <i class="material-symbols-rounded symbol-3x opacity-75">group</i>
            </div>
        </div>
    </div>
    @endif

    @if (Qs::userIsTeamSA())
    <!-- Support Team -->
    <div class="card card-body bg-danger-400 has-bg-image">
        <div class="media">
            <div class="media-body ml-2">
                <div class="row">
                    <div class="col-6 text-left">
                        {{ $staff->where('user_type', 'teacher')->count() }}
                        <br />
                        <span class="text-capitalize font-size-xs">Total Teachers</span>
                    </div>
                    <div class="col-6 text-left">
                        {{ $staff->where('user_type', 'companion')->count() }}
                        <br />
                        <span class="text-capitalize font-size-xs">Total Companions</span>
                    </div>
                </div>
            </div>
            <div class="ml-3 align-self-center d-none d-md-block">
                <i class="material-symbols-rounded symbol-3x opacity-75">group</i>
            </div>
        </div>
    </div>
    <!-- Accountants & Librarians -->
    <div class="card card-body bg-danger-400 has-bg-image">
        <div class="media">
            <div class="media-body ml-2">
                <div class="row">
                    <div class="col-6 text-left">
                        {{ $staff->where('user_type', 'accountant')->count() }}
                        <br />
                        <span class="text-capitalize font-size-xs">Total Accountants</span>
                    </div>
                    <div class="col-6 text-left">
                        {{ $staff->where('user_type', 'librarian')->count() }}
                        <br />
                        <span class="text-capitalize font-size-xs">Total Librarians</span>
                    </div>
                </div>
            </div>
            <div class="ml-3 align-self-center d-none d-md-block">
                <i class="material-symbols-rounded symbol-3x opacity-75">library_books</i>
            </div>
        </div>
    </div>
    <!-- Assessments -->
    <div class="card card-body bg-success-400 has-bg-image">
        <div class="media">
            <div class="media-body">
                <h3 class="mb-0 counter">{{ $assessments_count }}</h3>
                <span class="text-capitalize font-size-xs">Assesssments Declared</span>
            </div>

            <div class="ml-3 align-self-center">
                <i class="material-symbols-rounded symbol-3x opacity-75">folder_open</i>
            </div>
        </div>
    </div>
    <!-- Parents -->
    <div class="card card-body bg-indigo-400 has-bg-image">
        <div class="media">
            <div class="media-body">
                <div class="row">
                    <div class="col-4">
                        @php $parents = $users->where('user_type', 'parent') @endphp
                        <h3 class="mb-0 counter">{{ $parents->count() }}</h3>
                    </div>
                    <div class="col-6">
                        <div class="row">
                            <div class="col-6">
                                M:
                                {{ $parents->where('gender', 'Male')->count() }}
                            </div>
                            <div class="col-6">
                                F:
                                {{ $parents->where('gender', 'Female')->count() }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <span class="text-capitalize font-size-xs">Total Parents</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if (Qs::userIsTeamSA())
    <!-- Total subjects -->
    <div class="card card-body bg-brown-400 has-bg-image">
        <div class="media">
            <div class="media-body">
                <h3 class="mb-0 counter">{{ count($subjects->all()) }}</h3>
                <span class="text-capitalize font-size-xs">Total Subjects</span>
            </div>

            <div class="ml-3 align-self-center">
                <i class="material-symbols-rounded symbol-3x opacity-75">subject</i>
            </div>
        </div>
    </div>
    @endif

    <!-- Classes -->
    <div class="card card-body bg-orange-400 has-bg-image">
        <div class="media">
            <div class="media-body">
                <h3 class="mb-0 counter">{{ $classes_count }}</h3>
                <span class="text-capitalize font-size-xs">Total Classes</span>
            </div>

            <div class="ml-3 align-self-center">
                <i class="material-symbols-rounded symbol-3x opacity-75">domain</i>
            </div>
        </div>
    </div>

    @if (Qs::userIsTeamSA())
    <!-- Results -->
    <div class="card card-body bg-primary-400 has-bg-image">
        <div class="media">
            <div class="media-body">
                <h3 class="mb-0 counter">{{ $exams_count }}</h3>
                <span class="text-capitalize font-size-xs">Results Declared</span>
            </div>

            <div class="ml-3 align-self-center">
                <i class="material-symbols-rounded symbol-3x opacity-75">file_open</i>
            </div>
        </div>
    </div>
    @endif

    @if (Qs::userIsAdministrative())
    <!-- Payments -->
    <div class="card card-body bg-pink-400 has-bg-image">
        <div class="media">
            <div class="media-body">
                <h3 class="mb-0 counter">{{ $total_pay_records }}</h3>
                <span class="text-capitalize font-size-xs">Total Payments Done</span>
            </div>

            <div class="ml-3 align-self-center">
                <i class="material-symbols-rounded symbol-3x opacity-75">paid</i>
            </div>
        </div>
    </div>
    @endif

    @if (Qs::userIsTeamLibrary())
    <!-- Books -->
    <div class="card card-body bg-violet-400 has-bg-image">
        <div class="media">
            <div class="media-body">
                <h3 class="mb-0 counter">{{ $books_count }}</h3>
                <span class="text-capitalize font-size-xs">Total Books</span>
            </div>

            <div class="ml-3 align-self-center">
                <i class="material-symbols-rounded symbol-3x opacity-75">library_books</i>
            </div>
        </div>
    </div>

    <!-- Support Team -->
    <div class="card card-body bg-warning-400 has-bg-image">
        <div class="media">
            <div class="media-body ml-2">
                <div class="row">
                    <div class="col-6 text-left">
                        {{ $books_requests->where('returned', 1)->count() }}
                        <br />
                        <span class="text-capitalize font-size-xs">Returned</span>
                    </div>
                    <div class="col-6 text-left">
                        {{ $books_requests->where('returned', 0)->count() }}
                        <br />
                        <span class="text-capitalize font-size-xs">Out</span>
                    </div>
                </div>
            </div>
            <div class="media-body">
                <h3 class="mb-0 counter">{{ $books_requests->count() }}</h3>
                <span class="text-capitalize font-size-xs">Total Books Requests</span>
            </div>
        </div>
    </div>
    @endif
</div>
@endif

@if (Qs::userIsTeacher())
{{-- Teacher Workspace Overview --}}
<div class="card border-left-3 border-left-primary mb-3">
    <div class="card-header header-elements-inline">
        <h6 class="card-title font-weight-bold text-primary">
            <i class="material-symbols-rounded align-middle mr-1">school</i>
            My Teacher Workspace
        </h6>
        <div class="header-elements">
            <span class="badge badge-light border">Teacher Profile</span>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            {{-- Class Teacher Status Card --}}
            <div class="col-md-6 mb-3">
                <div class="card border h-100 shadow-none">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                        <h6 class="font-weight-semibold mb-0">
                            <i class="material-symbols-rounded font-size-sm align-middle mr-1 text-primary">meeting_room</i>
                            Class Teacher Assignments
                        </h6>
                        @if(isset($is_class_teacher) && $is_class_teacher)
                            <span class="badge badge-success">Assigned</span>
                        @else
                            <span class="badge badge-light">None</span>
                        @endif
                    </div>
                    <div class="card-body">
                        @if(isset($my_sections) && $my_sections->isNotEmpty())
                            <div class="list-group list-group-flush mb-3">
                                @foreach($my_sections as $sec)
                                    <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="text-dark">{{ $sec->my_class->name ?? 'Class' }} - {{ $sec->name }}</strong>
                                            <div class="text-muted font-size-xs">
                                                {{ $sec->student_record ? $sec->student_record->where('grad', 0)->count() : 0 }} Registered Students |
                                                {{ $sec->my_class ? $sec->my_class->subjects->count() : 0 }} Inherited Subjects
                                            </div>
                                        </div>
                                        <div class="btn-group">
                                            <a href="{{ route('teacher.class_detail', $sec->id) }}" class="btn btn-sm btn-outline-primary" title="View Class Roster">
                                                <i class="material-symbols-rounded font-size-sm">visibility</i>
                                            </a>
                                            <a href="{{ route('attendance.show', [$sec->id, date('Y-m-d')]) }}" class="btn btn-sm btn-outline-success ml-1" title="Mark Daily Attendance">
                                                <i class="material-symbols-rounded font-size-sm">edit_calendar</i>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <a href="{{ route('teacher.my_class') }}" class="btn btn-sm btn-primary btn-block">
                                <i class="material-symbols-rounded font-size-sm mr-1">meeting_room</i> Open Class Teacher Dashboard
                            </a>
                        @else
                            <p class="text-muted font-size-sm mb-0 py-2">
                                <i class="material-symbols-rounded font-size-sm align-middle text-muted mr-1">info</i>
                                You are not currently assigned as a Class Teacher to any class section.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Subject Teacher Status Card --}}
            <div class="col-md-6 mb-3">
                <div class="card border h-100 shadow-none">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                        <h6 class="font-weight-semibold mb-0">
                            <i class="material-symbols-rounded font-size-sm align-middle mr-1 text-primary">menu_book</i>
                            Subject Teacher Assignments
                        </h6>
                        @if(isset($is_subject_teacher) && $is_subject_teacher)
                            <span class="badge badge-success">Assigned</span>
                        @else
                            <span class="badge badge-light">None</span>
                        @endif
                    </div>
                    <div class="card-body">
                        @if(isset($my_subject_records) && $my_subject_records->isNotEmpty())
                            <div class="list-group list-group-flush mb-3">
                                @foreach($my_subject_records->take(4) as $srec)
                                    <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="text-dark">{{ $srec->subject->name ?? 'Subject' }}</strong>
                                            <div class="text-muted font-size-xs">
                                                Class: {{ $srec->section->my_class->name ?? ($srec->subject->my_class->name ?? 'All') }} - Section {{ $srec->section->name ?? 'All' }}
                                            </div>
                                        </div>
                                        @if($srec->section_id && $srec->subject_id)
                                            <a href="{{ route('teacher.subject_students', [$srec->subject_id, $srec->section_id]) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="material-symbols-rounded font-size-sm mr-1">groups</i> Students
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <a href="{{ route('teacher.my_subjects') }}" class="btn btn-sm btn-primary btn-block">
                                <i class="material-symbols-rounded font-size-sm mr-1">menu_book</i> View All Assigned Subjects ({{ $my_subject_records->count() }})
                            </a>
                        @else
                            <p class="text-muted font-size-sm mb-0 py-2">
                                <i class="material-symbols-rounded font-size-sm align-middle text-muted mr-1">info</i>
                                You are not currently assigned to any subjects.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Notices -->
<div class="row">
    <div class="col-12">
        {{-- Notices --}}
        <div class="card card-collapsed">
            <div class="card-header header-elements-inline">
                <h6 class="card-title">Notices<sup class="badge badge-default notices-badge">{{ $unviewed_count }}</sup></h6>
                <div class="header-elements">
                    <div class="list-icons">
                        @if(Qs::userIsAdministrative())
                        <a class="btn btn-link list-icons-item" href="{{ route('notices.index') }}">Manage</a>
                        @endif
                        <a class="list-icons-item" data-action="collapse"></a>
                        <a class="list-icons-item" data-action="remove"></a>
                    </div>
                </div>
            </div>
            <div class="notices">
                @include('pages/support_team/notices/show')
            </div>
        </div>
    </div>
</div>

{{--Students number breakdown Modal--}}
@include('pages/modals/students_number')

@endsection
