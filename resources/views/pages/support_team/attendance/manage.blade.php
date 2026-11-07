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
        <form method="POST" action="{{ route('attendance.store', [$section->id, $selected_date]) }}" id="attendance-form">
            @csrf

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center mb-2 mb-md-0">
                    <h6 class="font-weight-semibold mb-0 text-primary mr-3">
                        <i class="material-symbols-rounded align-middle mr-1">calendar_today</i>
                        Register for {{ $formatted_date }}
                    </h6>
                    <div id="save-status-indicator" style="display: none;"></div>
                </div>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-success mr-2 font-weight-semibold" onclick="markAll('present')">
                        <i class="material-symbols-rounded font-size-sm mr-1">done_all</i> Mark All Present
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger mr-3 font-weight-semibold" onclick="markAll('absent')">
                        <i class="material-symbols-rounded font-size-sm mr-1">close</i> Mark All Absent
                    </button>
                    <button type="submit" id="top-save-btn" class="btn btn-sm btn-primary btn-save-attendance font-weight-semibold px-3 py-1.5 shadow-sm" style="background: #2563eb; border-color: #2563eb; border-radius: 6px;">
                        <i class="material-symbols-rounded font-size-sm mr-1">save</i> Save Register
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
                                    <div class="btn-group attendance-toggle-group" role="group">
                                        <button type="button" class="btn btn-sm status-btn status-btn-present {{ $current_status === 'present' ? 'active' : '' }}" data-status="present" style="min-width: 96px; cursor: pointer;">
                                            <input type="radio" name="attendance[{{ $st->id }}]" value="present" id="present_{{ $st->id }}" {{ $current_status === 'present' ? 'checked' : '' }} style="display: none;">
                                            <i class="material-symbols-rounded font-size-sm align-middle mr-1" style="pointer-events: none;">check</i> Present
                                        </button>
                                        <button type="button" class="btn btn-sm status-btn status-btn-absent {{ $current_status === 'absent' ? 'active' : '' }}" data-status="absent" style="min-width: 96px; cursor: pointer;">
                                            <input type="radio" name="attendance[{{ $st->id }}]" value="absent" id="absent_{{ $st->id }}" {{ $current_status === 'absent' ? 'checked' : '' }} style="display: none;">
                                            <i class="material-symbols-rounded font-size-sm align-middle mr-1" style="pointer-events: none;">close</i> Absent
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4 pb-3">
                <span class="text-muted font-size-sm">
                    <i class="material-symbols-rounded align-middle mr-1">info</i>
                    Submitting this form updates the daily register and recalculates cumulative statistics automatically.
                </span>
                <button type="submit" id="bottom-save-btn" class="btn btn-primary btn-save-attendance px-4 py-2 font-weight-semibold shadow-sm" style="background: #2563eb; border-color: #2563eb; border-radius: 8px;">
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

<style>
.status-btn {
    transition: all 0.18s ease-in-out;
    user-select: none;
    font-weight: 500;
}
.status-btn-present {
    border: 1px solid rgba(34, 197, 94, 0.4) !important;
    color: #4ade80 !important;
    background-color: rgba(34, 197, 94, 0.08) !important;
}
.status-btn-present.active,
.status-btn-present:hover {
    background-color: #16a34a !important;
    border-color: #16a34a !important;
    color: #ffffff !important;
    box-shadow: 0 0 12px rgba(22, 163, 74, 0.35);
}
.status-btn-absent {
    border: 1px solid rgba(239, 68, 68, 0.4) !important;
    color: #f87171 !important;
    background-color: rgba(239, 68, 68, 0.08) !important;
}
.status-btn-absent.active,
.status-btn-absent:hover {
    background-color: #dc2626 !important;
    border-color: #dc2626 !important;
    color: #ffffff !important;
    box-shadow: 0 0 12px rgba(220, 38, 38, 0.35);
}
</style>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Individual button click toggle
        $(document).on('click', '.status-btn', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const status = $btn.data('status');
            const $group = $btn.closest('.attendance-toggle-group');

            $group.find('.status-btn').removeClass('active');
            $btn.addClass('active');
            $group.find('input[type="radio"]').prop('checked', false);
            $btn.find('input[type="radio"]').prop('checked', true).trigger('change');

            showUnsavedChanges();
        });

        // Global Mark All
        window.markAll = function(status) {
            $('.attendance-toggle-group').each(function() {
                const $group = $(this);
                $group.find('.status-btn').removeClass('active');
                const $targetBtn = $group.find('.status-btn-' + status);
                $targetBtn.addClass('active');
                $group.find('input[type="radio"]').prop('checked', false);
                $targetBtn.find('input[type="radio"]').prop('checked', true).trigger('change');
            });
            showUnsavedChanges();
        };

        function showUnsavedChanges() {
            $('#save-status-indicator').html(
                '<span class="badge badge-pill px-2.5 py-1 font-size-xs font-weight-semibold" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4);"><i class="material-symbols-rounded font-size-xs mr-1 align-middle">error_outline</i> Unsaved Changes</span>'
            ).stop(true, true).fadeIn();
        }

        // Dedicated AJAX form submit (avoids clearForm reset from ajax-store)
        $('#attendance-form').on('submit', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $saveBtns = $('.btn-save-attendance');
            const topOrigHtml = '<i class="material-symbols-rounded font-size-sm mr-1">save</i> Save Register';
            const bottomOrigHtml = '<i class="material-symbols-rounded mr-2">save</i> Save Attendance Register';

            $saveBtns.prop('disabled', true).html('<i class="material-symbols-rounded spinner mr-1 font-size-sm align-middle">progress_activity</i> Saving...');

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),
                dataType: 'json'
            }).done(function(res) {
                $saveBtns.prop('disabled', false);
                $('#top-save-btn').html(topOrigHtml);
                $('#bottom-save-btn').html(bottomOrigHtml);

                if (res.ok) {
                    flash({ msg: res.msg || 'Attendance recorded successfully!', type: 'success' });
                    $('#save-status-indicator').html(
                        '<span class="badge badge-pill px-2.5 py-1 font-size-xs font-weight-semibold" style="background: rgba(34, 197, 94, 0.2); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.4);"><i class="material-symbols-rounded font-size-xs mr-1 align-middle">check_circle</i> All Saved</span>'
                    ).stop(true, true).fadeIn();
                } else {
                    flash({ msg: res.msg || 'Failed to save attendance', type: 'error' });
                }
            }).fail(function(xhr) {
                $saveBtns.prop('disabled', false);
                $('#top-save-btn').html(topOrigHtml);
                $('#bottom-save-btn').html(bottomOrigHtml);
                flash({ msg: 'Error saving attendance register. Please check your connection.', type: 'error' });
            });
        });
    });
</script>
@endsection
