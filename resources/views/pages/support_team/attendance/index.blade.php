@extends('layouts.master')

@section('page_title', 'Attendance Management')

@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title"><i class="material-symbols-rounded mr-2">fact_check</i> Attendance Registers</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <div class="alert alert-info border-0 alert-dismissible mb-4">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            <span>
                <i class="material-symbols-rounded align-middle mr-1">info</i>
                Class teachers can mark daily attendance for their assigned classes. Attendance percentages and totals are automatically calculated.
            </span>
        </div>

        <div class="row">
            @forelse($sections as $sec)
                @php
                    $students_count = $sec->student_record ? $sec->student_record->where('grad', 0)->count() : 0;
                    $is_my_section = (int)$sec->teacher_id === (int)auth()->id();
                @endphp
                <div class="col-md-6 col-xl-4">
                    <div class="card border-left-3 {{ $is_my_section ? 'border-left-primary shadow-sm' : 'border-left-secondary' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="card-title font-weight-bold mb-0">
                                    {{ $sec->my_class->name ?? 'Class' }} - {{ $sec->name }}
                                </h5>
                                @if($is_my_section)
                                    <span class="badge badge-primary">My Class</span>
                                @endif
                            </div>

                            <p class="text-muted font-size-sm mb-3">
                                <strong>Teacher:</strong> {{ $sec->teacher->name ?? 'Not Assigned' }}<br>
                                <strong>Active Students:</strong> <span class="badge badge-light badge-striped">{{ $students_count }}</span>
                            </p>

                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                <a href="{{ route('attendance.show', [$sec->id, $today]) }}" class="btn btn-sm btn-primary">
                                    <i class="material-symbols-rounded font-size-sm mr-1">edit_calendar</i> Mark Today
                                </a>
                                <a href="{{ route('attendance.section_report', $sec->id) }}" class="btn btn-sm btn-light">
                                    <i class="material-symbols-rounded font-size-sm mr-1">analytics</i> Report
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning border-0">
                        <i class="material-symbols-rounded align-middle mr-1">warning</i> No classes/sections available for attendance.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

@endsection
