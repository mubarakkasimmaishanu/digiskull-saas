@extends('layouts.master')

@section('page_title', 'Mark Attendance - ' . ($my_class->name ?? '') . ' ' . ($section->name ?? ''))

@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <div>
            <h5 class="card-title font-weight-bold mb-0">
                <i class="material-symbols-rounded align-middle mr-1 text-primary">fact_check</i>
                Daily Attendance Register: {{ $my_class->name }} - {{ $section->name }}
            </h5>
            <span class="text-muted font-size-sm">Class Teacher: {{ $section->teacher->name ?? 'Not Assigned' }} | Total Enrolled: {{ $students->count() }}</span>
        </div>
        <div class="header-elements">
            <a href="{{ route('attendance.section_report', $section->id) }}" class="btn btn-outline-primary btn-sm mr-2">
                <i class="material-symbols-rounded mr-1 font-size-sm">analytics</i> Cumulative Report
            </a>
            {!! Qs::getPanelOptions() !!}
        </div>
    </div>

    <div class="card-body">
        {{-- Date Selector Bar --}}
        <div class="row bg-light rounded p-3 mb-4 align-items-center">
            <div class="col-md-5 mb-2 mb-md-0">
                <form method="GET" action="" id="dateChangeForm" class="d-flex align-items-center">
                    <label for="register_date" class="font-weight-semibold mr-2 mb-0 text-nowrap">Register Date:</label>
                    <input type="date" id="register_date" class="form-control" value="{{ $selected_date }}" max="{{ date('Y-m-d') }}" onchange="window.location.href='{{ url('attendance/'.$section->id) }}/' + this.value">
                </form>
            </div>
            <div class="col-md-7 text-md-right">
                <span class="text-muted mr-2 font-size-sm font-weight-bold">Recent Dates:</span>
                @forelse($recent_dates as $rd)
                    <a href="{{ route('attendance.show', [$section->id, $rd]) }}" class="badge {{ $rd === $selected_date ? 'badge-primary' : 'badge-light' }} p-2 mr-1">
                        {{ \Carbon\Carbon::parse($rd)->format('M d') }}
                    </a>
                @empty
                    <span class="text-muted font-size-sm font-italic">No prior dates recorded</span>
                @endforelse
            </div>
        </div>

        {{-- Attendance Marking Form --}}
        @if($students->count() > 0)
        <form method="POST" action="{{ route('attendance.store', [$section->id, $selected_date]) }}" id="attendance-form" class="ajax-store">
            @csrf

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="font-weight-semibold mb-0 text-primary">
                    <i class="material-symbols-rounded align-middle mr-1">calendar_today</i>
                    Register for {{ $formatted_date }}
                </h6>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-success mr-1" onclick="markAll('present')">
                        <i class="material-symbols-rounded font-size-sm mr-1">done_all</i> Mark All Present
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="markAll('absent')">
                        <i class="material-symbols-rounded font-size-sm mr-1">close</i> Mark All Absent
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="bg-slate-800 text-white">
                        <tr>
                            <th style="width: 50px;">S/N</th>
                            <th style="width: 70px;">Photo</th>
                            <th>Admission No</th>
                            <th>Student Name</th>
                            <th>Gender</th>
                            <th class="text-center" style="width: 250px;">Status ({{ $selected_date }})</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $st)
                            @php
                                $existing_record = $attendance_records->get($st->id);
                                $current_status = $existing_record ? $existing_record->status : 'present';
                            @endphp
                            <tr>
                                <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                                <td class="text-center">
                                    <img class="rounded-circle" width="36" height="36" src="{{ Usr::getTenantAwarePhoto($st->user->photo) }}" alt="photo">
                                </td>
                                <td class="font-weight-semibold text-primary">
                                    {{ $st->adm_no ?: ($st->user->username ?? '-') }}
                                </td>
                                <td class="font-weight-bold">
                                    {{ $st->user->name ?? 'N/A' }}
                                </td>
                                <td>{{ $st->user->gender ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                        <label class="btn btn-sm btn-outline-success status-btn {{ $current_status === 'present' ? 'active' : '' }}" style="cursor: pointer; min-width: 90px;">
                                            <input type="radio" name="attendance[{{ $st->id }}]" value="present" id="present_{{ $st->id }}" {{ $current_status === 'present' ? 'checked' : '' }}>
                                            <i class="material-symbols-rounded font-size-sm align-middle mr-1">check</i> Present
                                        </label>
                                        <label class="btn btn-sm btn-outline-danger status-btn {{ $current_status === 'absent' ? 'active' : '' }}" style="cursor: pointer; min-width: 90px;">
                                            <input type="radio" name="attendance[{{ $st->id }}]" value="absent" id="absent_{{ $st->id }}" {{ $current_status === 'absent' ? 'checked' : '' }}>
                                            <i class="material-symbols-rounded font-size-sm align-middle mr-1">close</i> Absent
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <span class="text-muted font-size-sm">
                    <i class="material-symbols-rounded align-middle mr-1">info</i>
                    Submitting this form updates the daily register and recalculates cumulative statistics automatically.
                </span>
                <button type="submit" class="btn btn-primary px-4 py-2 font-weight-semibold">
                    <i class="material-symbols-rounded mr-2">save</i> Save Attendance Register
                </button>
            </div>
        </form>
        @else
            <div class="alert alert-warning border-0">
                <i class="material-symbols-rounded align-middle mr-1">warning</i> No active students currently enrolled in this class/section.
            </div>
        @endif
    </div>
</div>

<script>
    function markAll(status) {
        document.querySelectorAll('input[type="radio"][value="' + status + '"]').forEach(function(radio) {
            radio.checked = true;
            var label = radio.closest('label');
            if (label) {
                var btnGroup = label.closest('.btn-group');
                if (btnGroup) {
                    btnGroup.querySelectorAll('label').forEach(function(l) { l.classList.remove('active'); });
                }
                label.classList.add('active');
            }
        });
    }
</script>

@endsection
