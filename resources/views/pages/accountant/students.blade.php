@extends('layouts.master')
@section('page_title', 'Student Financial Directory')
@section('content')

<div class="card shadow-sm">
    <div class="card-header bg-white header-elements-inline py-3 border-bottom">
        <div>
            <h5 class="card-title font-weight-bold mb-1 text-primary">
                <i class="icon-users4 mr-2"></i> Student Financial Directory &amp; Records
            </h5>
            <span class="text-muted small">
                Search students, inspect guardian information, review fee statuses, and record fee payments.
            </span>
        </div>
        <div class="header-elements">
            <a href="{{ route('accountant.dashboard') }}" class="btn btn-sm btn-light border mr-2">
                <i class="icon-arrow-left52 mr-1"></i> Dashboard
            </a>
            <a href="{{ route('accountant.invoices') }}" class="btn btn-sm btn-outline-primary mr-2 font-weight-semibold">
                <i class="icon-file-text2 mr-1"></i> Manage Invoices
            </a>
            <a href="{{ route('accountant.sms_reminders') }}" class="btn btn-sm btn-danger font-weight-semibold shadow-sm">
                <i class="icon-mobile mr-1"></i> Bulk SMS Reminders
            </a>
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="card-body bg-light border-bottom py-3">
        <form method="GET" action="{{ route('accountant.students') }}">
            <div class="row align-items-end">
                <div class="col-md-3 mb-2">
                    <label class="font-weight-semibold font-size-xs text-muted text-uppercase mb-1">Search Student</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Name or Admission No..." value="{{ $search }}">
                </div>

                <div class="col-md-3 mb-2">
                    <label class="font-weight-semibold font-size-xs text-muted text-uppercase mb-1">Class</label>
                    <select name="class_id" class="form-control form-control-sm">
                        <option value="">All Classes</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $selected_class == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-2">
                    <label class="font-weight-semibold font-size-xs text-muted text-uppercase mb-1">Payment Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">All Statuses</option>
                        <option value="paid" {{ $selected_status == 'paid' ? 'selected' : '' }}>Paid in Full</option>
                        <option value="partially_paid" {{ $selected_status == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="not_paid" {{ $selected_status == 'not_paid' ? 'selected' : '' }}>Not Paid</option>
                        <option value="no_fees" {{ $selected_status == 'no_fees' ? 'selected' : '' }}>No Fees Billed</option>
                    </select>
                </div>

                <div class="col-md-3 mb-2 d-flex">
                    <button type="submit" class="btn btn-sm btn-primary font-weight-semibold mr-2 flex-grow-1">
                        <i class="icon-search4 mr-1"></i> Filter
                    </button>
                    <a href="{{ route('accountant.students') }}" class="btn btn-sm btn-light border">Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Students Table --}}
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered mb-0">
            <thead class="bg-light text-muted font-size-xs text-uppercase">
                <tr>
                    <th style="width: 45px;" class="text-center">#</th>
                    <th style="width: 120px;">Adm No</th>
                    <th>Student Name</th>
                    <th>Class &amp; Section</th>
                    <th>Guardian Details</th>
                    <th class="text-right">Billed</th>
                    <th class="text-right text-success">Paid</th>
                    <th class="text-right text-danger">Outstanding</th>
                    <th class="text-center">Payment Status</th>
                    <th class="text-center" style="width: 140px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $idx => $item)
                    @php
                        $sr = $item['sr'];
                        $st = $sr->user;
                        $guardianPhone = $sr->my_parent ? ($sr->my_parent->phone ?: $sr->my_parent->phone2) : ($sr->phone ?: $st->phone);
                        $guardianName = $sr->my_parent ? $sr->my_parent->name : 'Guardian';
                    @endphp
                    <tr>
                        <td class="text-center text-muted font-weight-semibold">{{ $idx + 1 }}</td>
                        <td class="font-weight-semibold text-primary">
                            <span class="badge badge-light border">{{ $sr->adm_no ?: ($st->username ?: '—') }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ Usr::getTenantAwarePhoto($st->photo) }}" class="rounded-circle mr-2 border" width="32" height="32" alt="">
                                <div>
                                    <span class="font-weight-bold text-dark">{{ $st->name }}</span>
                                    <div class="small text-muted">{{ $st->gender ?: '—' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-light border">{{ $sr->my_class->name }}</span>
                            <span class="badge badge-secondary">{{ $sr->section->name }}</span>
                        </td>
                        <td>
                            <div class="font-weight-semibold text-dark">{{ $guardianName }}</div>
                            <div class="small text-muted">
                                <i class="icon-phone mr-1"></i> {{ $guardianPhone ?: 'No phone recorded' }}
                            </div>
                        </td>
                        <td class="text-right font-weight-semibold text-dark">
                            ₦{{ number_format($item['total_billed']) }}
                        </td>
                        <td class="text-right font-weight-bold text-success">
                            ₦{{ number_format($item['total_paid']) }}
                        </td>
                        <td class="text-right font-weight-bold text-danger">
                            ₦{{ number_format($item['total_balance']) }}
                        </td>
                        <td class="text-center">
                            @if($item['status'] === 'paid')
                                <span class="badge badge-success px-2 py-1"><i class="icon-checkmark-circle mr-1"></i> Paid in Full</span>
                            @elseif($item['status'] === 'partially_paid')
                                <span class="badge badge-warning px-2 py-1 text-dark"><i class="icon-pie5 mr-1"></i> Partially Paid</span>
                            @elseif($item['status'] === 'not_paid')
                                <span class="badge badge-danger px-2 py-1"><i class="icon-cross2 mr-1"></i> Not Paid</span>
                            @else
                                <span class="badge badge-secondary px-2 py-1">No Fees Billed</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('accountant.student_fees', $st->id) }}" class="btn btn-xs btn-primary font-weight-semibold shadow-sm">
                                <i class="icon-cash mr-1"></i> Manage Fees
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            <i class="icon-users4 icon-2x mb-2 text-muted"></i>
                            <p class="mb-0">No student records found matching your search filters.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
