@extends('layouts.master')

@section('page_title', 'Attendance Statistics - ' . ($my_class->name ?? '') . ' ' . ($section->name ?? ''))

@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <div>
            <h5 class="card-title font-weight-bold mb-0">
                <i class="material-symbols-rounded align-middle mr-1 text-primary">analytics</i>
                Attendance Statistics: {{ $my_class->name }} - {{ $section->name }}
            </h5>
            <span class="text-muted font-size-sm">Session: {{ $session }} | Total School Days Recorded: <strong>{{ $report['total_school_days'] }}</strong></span>
        </div>
        <div class="header-elements">
            <a href="{{ route('attendance.show', [$section->id, date('Y-m-d')]) }}" class="btn btn-primary btn-sm mr-2">
                <i class="material-symbols-rounded mr-1 font-size-sm">edit_calendar</i> Mark Daily Register
            </a>
            {!! Qs::getPanelOptions() !!}
        </div>
    </div>

    <div class="card-body">
        {{-- Stat metric cards --}}
        <div class="row mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card card-body bg-primary text-white has-bg-image">
                    <div class="media">
                        <div class="mr-3 align-self-center">
                            <i class="material-symbols-rounded font-size-40">calendar_month</i>
                        </div>
                        <div class="media-body text-right">
                            <h3 class="font-weight-semibold mb-0">{{ $report['total_school_days'] }}</h3>
                            <span class="text-uppercase font-size-xs">Days School Opened</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card card-body bg-slate-800 text-white has-bg-image">
                    <div class="media">
                        <div class="mr-3 align-self-center">
                            <i class="material-symbols-rounded font-size-40">groups</i>
                        </div>
                        <div class="media-body text-right">
                            <h3 class="font-weight-semibold mb-0">{{ count($report['students_summary']) }}</h3>
                            <span class="text-uppercase font-size-xs">Enrolled Students</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-6">
                <div class="card card-body bg-light border">
                    <div class="d-flex align-items-center">
                        <i class="material-symbols-rounded text-info font-size-32 mr-3">calculate</i>
                        <div>
                            <span class="font-weight-bold text-dark">Automated Calculation Formula:</span><br>
                            <span class="text-muted font-size-sm font-italic">
                                <strong>Attendance Percentage</strong> = (Present Days &divide; Days School Opened) &times; 100
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistics Table --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover datatable-button-html5-columns">
                <thead class="bg-slate-800 text-white">
                    <tr>
                        <th style="width: 50px;">S/N</th>
                        <th>Adm No</th>
                        <th>Student Name</th>
                        <th>Gender</th>
                        <th class="text-center">Days Opened</th>
                        <th class="text-center text-success font-weight-bold">Days Present</th>
                        <th class="text-center text-danger font-weight-bold">Days Absent</th>
                        <th class="text-center" style="width: 200px;">Attendance %</th>
                        <th class="text-center" style="width: 80px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report['students_summary'] as $item)
                        @php
                            $st = $item['student'];
                            $pct = $item['percentage'];
                            $badge_class = $pct >= 75 ? 'badge-success' : ($pct >= 50 ? 'badge-warning' : 'badge-danger');
                            $progress_class = $pct >= 75 ? 'bg-success' : ($pct >= 50 ? 'bg-warning' : 'bg-danger');
                        @endphp
                        <tr>
                            <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                            <td class="font-weight-semibold text-primary">
                                {{ $st->adm_no ?: ($st->user->username ?? '-') }}
                            </td>
                            <td class="font-weight-bold">
                                {{ $st->user->name ?? 'N/A' }}
                            </td>
                            <td>{{ $st->user->gender ?? 'N/A' }}</td>
                            <td class="text-center font-weight-semibold">{{ $item['total_days'] }}</td>
                            <td class="text-center font-weight-bold text-success">{{ $item['present_days'] }}</td>
                            <td class="text-center font-weight-bold text-danger">{{ $item['absent_days'] }}</td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="progress flex-grow-1 mr-2" style="height: 10px;">
                                        <div class="progress-bar {{ $progress_class }}" role="progressbar" style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <span class="badge {{ $badge_class }} font-weight-bold">{{ $pct }}%</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('attendance.student_report', $st->id) }}" class="btn btn-sm btn-outline-primary" title="View Student Breakdown">
                                    <i class="material-symbols-rounded font-size-sm">visibility</i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">No attendance data found for this class.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
