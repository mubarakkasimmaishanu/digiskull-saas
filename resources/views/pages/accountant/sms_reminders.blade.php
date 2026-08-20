@extends('layouts.master')
@section('page_title', 'Bulk SMS Fee Reminders')
@section('content')

<div class="card shadow-sm">
    <div class="card-header bg-white header-elements-inline py-3 border-bottom">
        <div>
            <h5 class="card-title font-weight-bold mb-1 text-danger">
                <i class="icon-mobile mr-2"></i> Bulk SMS Fee Reminders Center
            </h5>
            <span class="text-muted small">
                Filter students with outstanding fees, auto-extract guardian phone numbers, customize reminder templates, and dispatch in bulk.
            </span>
        </div>
        <div class="header-elements">
            <a href="{{ route('accountant.dashboard') }}" class="btn btn-sm btn-light border mr-2">
                <i class="icon-arrow-left52 mr-1"></i> Dashboard
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card-body bg-light border-bottom py-3">
        <form method="GET" action="{{ route('accountant.sms_reminders') }}">
            <div class="row align-items-end">
                <div class="col-md-4 mb-2">
                    <label class="font-weight-semibold font-size-xs text-muted text-uppercase mb-1">Class Filter</label>
                    <select name="class_id" class="form-control form-control-sm">
                        <option value="">All Classes</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $selected_class == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 mb-2">
                    <label class="font-weight-semibold font-size-xs text-muted text-uppercase mb-1">Fee Status Filter</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">All Debtors (Unpaid &amp; Partial)</option>
                        <option value="not_paid" {{ $selected_status == 'not_paid' ? 'selected' : '' }}>Not Paid Only</option>
                        <option value="partially_paid" {{ $selected_status == 'partially_paid' ? 'selected' : '' }}>Partially Paid Only</option>
                    </select>
                </div>

                <div class="col-md-4 mb-2 d-flex">
                    <button type="submit" class="btn btn-sm btn-primary font-weight-semibold mr-2 flex-grow-1">
                        <i class="icon-filter3 mr-1"></i> Filter Debtors
                    </button>
                    <a href="{{ route('accountant.sms_reminders') }}" class="btn btn-sm btn-light border">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <form method="POST" action="{{ route('accountant.send_bulk_sms') }}">
        @csrf

        <div class="row m-0">
            {{-- Left Side: Debtors Table --}}
            <div class="col-md-8 p-0 border-right">
                <div class="p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="selectAllDebtors">
                        <label class="custom-control-label font-weight-bold text-dark" for="selectAllDebtors">
                            Select All Debtors (<span id="selectedCount">0</span> of {{ count($debtors) }} selected)
                        </label>
                    </div>
                    <span class="text-muted small">Session: <strong>{{ $year }}</strong></span>
                </div>

                <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="bg-light text-muted font-size-xs text-uppercase sticky-top">
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th>Student Name</th>
                                <th>Class</th>
                                <th class="text-right">Outstanding</th>
                                <th>Guardian &amp; Phone</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($debtors as $idx => $dItem)
                                @php
                                    $hasPhone = !empty($dItem['guardian_phone']);
                                    $recipientPayload = json_encode([
                                        'phone' => $dItem['guardian_phone'],
                                        'name' => $dItem['guardian_name'],
                                        'student_name' => $dItem['student']->name,
                                        'balance' => $dItem['total_balance'],
                                    ]);
                                @endphp
                                <tr class="{{ !$hasPhone ? 'table-secondary opacity-75' : '' }}">
                                    <td class="text-center">
                                        @if($hasPhone)
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" name="recipients[]" value="{{ $recipientPayload }}" class="custom-control-input debtor-checkbox" id="debtor-{{ $idx }}">
                                                <label class="custom-control-label" for="debtor-{{ $idx }}"></label>
                                            </div>
                                        @else
                                            <span class="text-muted" title="No phone number recorded">&times;</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="font-weight-bold text-dark">{{ $dItem['student']->name }}</span>
                                        <div class="small text-muted">Adm: {{ $dItem['student_record']->adm_no ?: $dItem['student']->username }}</div>
                                    </td>
                                    <td>
                                        <span class="badge badge-light border">{{ $dItem['my_class']->name }}</span>
                                        <span class="badge badge-secondary">{{ $dItem['section']->name }}</span>
                                    </td>
                                    <td class="text-right font-weight-bold text-danger">
                                        ₦{{ number_format($dItem['total_balance']) }}
                                    </td>
                                    <td>
                                        <div class="font-weight-semibold text-dark">{{ $dItem['guardian_name'] }}</div>
                                        @if($hasPhone)
                                            <span class="badge badge-light border text-primary">
                                                <i class="icon-phone2 mr-1"></i> {{ $dItem['guardian_phone'] }}
                                            </span>
                                        @else
                                            <span class="badge badge-danger font-weight-normal">No phone number</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-success py-5">
                                        <i class="icon-checkmark-circle icon-2x mb-2 text-success"></i>
                                        <p class="mb-0 font-weight-semibold">No students found with outstanding school fees for this criteria.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Right Side: Message Template & Controls --}}
            <div class="col-md-4 p-4 bg-light">
                <h6 class="font-weight-bold text-dark mb-3">
                    <i class="icon-bubbles4 mr-1 text-primary"></i> Compose Reminder Message
                </h6>

                <div class="form-group">
                    <label class="font-weight-semibold font-size-xs text-uppercase text-muted">Sender ID:</label>
                    <input type="text" name="sender_id" class="form-control form-control-sm" placeholder="e.g. DIGISKUL" value="{{ substr(Qs::getSetting('system_name') ?: 'DIGISKUL', 0, 11) }}">
                </div>

                <div class="form-group">
                    <label class="font-weight-semibold font-size-xs text-uppercase text-muted">Message Template: <span class="text-danger">*</span></label>
                    <textarea name="message" id="messageBox" rows="6" class="form-control" placeholder="Enter reminder message..." required>Dear {name}, this is a gentle reminder from {school} regarding the outstanding school fees of ₦{balance} for your ward {student}. Kindly make payment before terminal examinations to avoid report-card withholding. Thank you.</textarea>
                </div>

                <div class="p-2 bg-white rounded border mb-3 small">
                    <strong>Dynamic Placeholders:</strong><br>
                    <code>{name}</code> &bull; Guardian Name<br>
                    <code>{student}</code> &bull; Student Name<br>
                    <code>{balance}</code> &bull; Outstanding Balance (₦)<br>
                    <code>{school}</code> &bull; School Name
                </div>

                <button type="submit" class="btn btn-danger btn-block font-weight-semibold shadow-sm py-2" id="sendBtn" onclick="return confirm('Are you sure you want to dispatch bulk SMS reminders to all selected guardian phone numbers?');">
                    <i class="icon-paperplane mr-1"></i> Send Bulk SMS Reminders
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var selectAll = document.getElementById('selectAllDebtors');
    var checkboxes = document.querySelectorAll('.debtor-checkbox');
    var counter = document.getElementById('selectedCount');

    function updateCount() {
        var checkedCount = document.querySelectorAll('.debtor-checkbox:checked').length;
        if (counter) counter.textContent = checkedCount;
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(function(cb) {
                cb.checked = selectAll.checked;
            });
            updateCount();
        });
    }

    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', updateCount);
    });

    updateCount();
});
</script>

@endsection
