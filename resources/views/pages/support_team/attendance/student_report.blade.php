@extends('layouts.master')

@section('page_title', 'Student Attendance - ' . ($sr->user->name ?? ''))

@section('content')

<div class="row">
    {{-- Student Summary Card --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <img src="{{ Usr::getTenantAwarePhoto($sr->user->photo) }}" width="96" height="96" class="rounded-circle shadow mb-3" alt="photo">
                <h5 class="font-weight-bold mb-0">{{ $sr->user->name }}</h5>
                <span class="text-muted font-size-sm">{{ $sr->adm_no ?: $sr->user->username }}</span>

                <div class="border-top mt-3 pt-3 text-left">
                    <p class="mb-2"><strong>Class:</strong> {{ $sr->my_class->name ?? 'N/A' }} - {{ $sr->section->name ?? 'N/A' }}</p>
                    <p class="mb-2"><strong>Gender:</strong> {{ $sr->user->gender ?? 'N/A' }}</p>
                    <p class="mb-2"><strong>Date of Birth:</strong> {{ $sr->user->dob ?? 'N/A' }}</p>
                    <p class="mb-2"><strong>Guardian:</strong> {{ $sr->my_parent->name ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>Guardian Phone:</strong> {{ $sr->my_parent->phone ?? ($sr->my_parent->phone2 ?? 'N/A') }}</p>
                </div>
            </div>
        </div>

        {{-- Stat Summary --}}
        <div class="card">
            <div class="card-header header-elements-inline">
                <h6 class="card-title font-weight-bold">Summary Metrics</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Total Days Opened
                        <span class="badge badge-primary badge-pill font-size-sm">{{ $stats['total_days'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Days Present
                        <span class="badge badge-success badge-pill font-size-sm">{{ $stats['present_days'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Days Absent
                        <span class="badge badge-danger badge-pill font-size-sm">{{ $stats['absent_days'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center font-weight-bold">
                        Attendance Percentage
                        <span class="badge {{ $stats['percentage'] >= 75 ? 'badge-success' : ($stats['percentage'] >= 50 ? 'badge-warning' : 'badge-danger') }} badge-pill font-size-sm">
                            {{ $stats['percentage'] }}%
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Attendance History Log --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header header-elements-inline">
                <h5 class="card-title font-weight-bold mb-0">
                    <i class="material-symbols-rounded align-middle mr-1 text-primary">history</i>
                    Attendance Log History
                </h5>
                <div class="header-elements">
                    <a href="{{ route('attendance.show', [$sr->section_id, date('Y-m-d')]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="material-symbols-rounded mr-1 font-size-sm">arrow_back</i> Back to Register
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover datatable-button-html5-columns">
                        <thead class="bg-slate-800 text-white">
                            <tr>
                                <th style="width: 50px;">S/N</th>
                                <th>Date</th>
                                <th>Day</th>
                                <th class="text-center" style="width: 120px;">Status</th>
                                <th>Recorded By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $rec)
                                <tr>
                                    <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                                    <td class="font-weight-semibold">{{ \Carbon\Carbon::parse($rec->date)->format('Y-m-d') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($rec->date)->format('l') }}</td>
                                    <td class="text-center">
                                        @if($rec->status === 'present')
                                            <span class="badge badge-success px-2 py-1"><i class="material-symbols-rounded font-size-xs align-middle mr-1">check</i> Present</span>
                                        @else
                                            <span class="badge badge-danger px-2 py-1"><i class="material-symbols-rounded font-size-xs align-middle mr-1">close</i> Absent</span>
                                        @endif
                                    </td>
                                    <td class="text-muted font-size-sm">{{ $rec->marked_by_user->name ?? 'System' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No attendance records logged yet for this student.</td>
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
