@extends('layouts.master')

@section('page_title', 'Class Teacher Dashboard - My Assigned Classes')

@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <div>
            <h5 class="card-title font-weight-bold mb-0">
                <i class="material-symbols-rounded align-middle mr-1 text-primary">meeting_room</i>
                Class Teacher Dashboard: Assigned Classes
            </h5>
            <span class="text-muted font-size-sm">Manage student rosters, view inherited class subjects, and record daily attendance</span>
        </div>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <div class="alert alert-info border-0 alert-dismissible mb-4">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            <div class="d-flex align-items-start">
                <i class="material-symbols-rounded mr-2 font-size-24">info</i>
                <div>
                    <strong>Class Teacher Role:</strong> You are assigned as the class teacher for the class(es) below.
                    Opening a class gives you access to the registered student roster, guardian information, and daily attendance register.
                    <div class="mt-1 font-italic opacity-75">
                        * All subjects configured for your class are automatically inherited by the registered students.
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            @forelse($sections_data as $data)
                @php
                    $sec = $data['section'];
                @endphp
                <div class="col-md-6 col-xl-4">
                    <div class="card border shadow-sm rounded-lg">
                        <div class="card-header bg-slate-800 text-white d-flex justify-content-between align-items-center py-2">
                            <h6 class="font-weight-bold mb-0 text-white">
                                {{ $data['class_name'] }}
                            </h6>
                            <span class="badge badge-primary">Class Teacher</span>
                        </div>

                        <div class="card-body">
                            <ul class="list-group list-group-flush mb-3">
                                <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                                    <span><i class="material-symbols-rounded align-middle mr-1 font-size-sm text-muted">groups</i> Registered Students:</span>
                                    <span class="badge badge-secondary badge-pill font-size-sm font-weight-bold">{{ $data['students_count'] }}</span>
                                </li>
                                <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                                    <span><i class="material-symbols-rounded align-middle mr-1 font-size-sm text-muted">menu_book</i> Inherited Subjects:</span>
                                    <span class="badge badge-secondary badge-pill font-size-sm font-weight-bold">{{ $data['inherited_subjects_count'] }}</span>
                                </li>
                                <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                                    <span><i class="material-symbols-rounded align-middle mr-1 font-size-sm text-muted">calendar_today</i> Today's Register:</span>
                                    @if($data['attendance_marked_today'])
                                        <span class="badge badge-success px-2 py-1"><i class="material-symbols-rounded font-size-xs mr-1 align-middle">check</i> Marked</span>
                                    @else
                                        <span class="badge badge-warning px-2 py-1"><i class="material-symbols-rounded font-size-xs mr-1 align-middle">schedule</i> Pending</span>
                                    @endif
                                </li>
                                <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                                    <span><i class="material-symbols-rounded align-middle mr-1 font-size-sm text-muted">history</i> Total Days Recorded:</span>
                                    <span class="font-weight-bold">{{ $data['total_school_days'] }}</span>
                                </li>
                            </ul>

                            <div class="d-flex flex-column gap-2 mt-2">
                                <a href="{{ route('teacher.class_detail', $sec->id) }}" class="btn btn-primary btn-block">
                                    <i class="material-symbols-rounded mr-1 font-size-sm">visibility</i> View Class &amp; Students
                                </a>
                                <div class="d-flex justify-content-between mt-2">
                                    <a href="{{ route('attendance.show', [$sec->id, $today]) }}" class="btn btn-sm btn-outline-success flex-grow-1 mr-1">
                                        <i class="material-symbols-rounded mr-1 font-size-sm">edit_calendar</i> Mark Attendance
                                    </a>
                                    <a href="{{ route('attendance.section_report', $sec->id) }}" class="btn btn-sm btn-outline-secondary flex-grow-1 ml-1">
                                        <i class="material-symbols-rounded mr-1 font-size-sm">analytics</i> Stats Report
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning border-0 text-center py-4">
                        <i class="material-symbols-rounded font-size-48 text-warning mb-2">assignment_late</i>
                        <h6 class="font-weight-bold">No Classes Assigned As Class Teacher</h6>
                        <p class="text-muted mb-0">You are not currently designated as the class teacher for any section. An administrator can assign class teachers in the Class/Sections configuration.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

@endsection
