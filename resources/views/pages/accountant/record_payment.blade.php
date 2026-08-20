@extends('layouts.master')
@section('page_title', 'Student Fee Management: ' . $student->name)
@section('content')

{{-- Student Profile & Financial Status Header --}}
<div class="card shadow-sm mb-4">
    <div class="card-body p-3 bg-white">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center my-2">
                <img src="{{ Usr::getTenantAwarePhoto($student->photo) }}" class="rounded-circle mr-3 border shadow-sm" width="60" height="60" alt="">
                <div>
                    <h5 class="font-weight-bold mb-1 text-dark">{{ $student->name }}</h5>
                    <div class="text-muted small">
                        <span class="font-weight-semibold text-primary">Adm No: {{ $sr->adm_no ?: ($student->username ?: '—') }}</span> &bull; 
                        Class: <strong>{{ $sr->my_class->name }} ({{ $sr->section->name }})</strong> &bull; 
                        Session: <span class="badge badge-secondary">{{ $year }}</span>
                    </div>
                    @php
                        $guardianPhone = $sr->my_parent ? ($sr->my_parent->phone ?: $sr->my_parent->phone2) : ($sr->phone ?: $student->phone);
                        $guardianName = $sr->my_parent ? $sr->my_parent->name : 'Guardian';
                    @endphp
                    <div class="small text-muted mt-1">
                        <i class="icon-user mr-1"></i> Guardian: <strong>{{ $guardianName }}</strong> &bull; 
                        <i class="icon-phone mr-1"></i> <strong>{{ $guardianPhone ?: 'No phone' }}</strong>
                    </div>
                </div>
            </div>

            <div class="my-2 text-md-right">
                <div class="mb-2">
                    <span class="text-muted small mr-2">Report Card Clearance:</span>
                    @if($summary['is_cleared'])
                        <span class="badge badge-success px-3 py-2 font-weight-bold font-size-sm">
                            <i class="icon-checkmark-circle mr-1"></i> Cleared (Report Card Unlocked)
                        </span>
                    @else
                        <span class="badge badge-danger px-3 py-2 font-weight-bold font-size-sm">
                            <i class="icon-lock mr-1"></i> Locked (₦{{ number_format($summary['total_balance']) }} Outstanding)
                        </span>
                    @endif
                </div>

                <a href="{{ route('accountant.students') }}" class="btn btn-sm btn-light border mr-1">
                    <i class="icon-arrow-left52 mr-1"></i> Back to Students
                </a>
                <a href="{{ route('exam_officer.report_card', [$student->id, 1, $year]) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                    <i class="icon-file-text2 mr-1"></i> Preview Report Card
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Financial Summary KPIs --}}
<div class="row">
    <div class="col-md-4">
        <div class="card card-body bg-white shadow-sm border-left-3 border-left-primary py-3 mb-3">
            <span class="text-uppercase font-size-xs text-muted font-weight-semibold">Total Fees Invoiced</span>
            <h3 class="font-weight-bold mb-0 text-primary">₦{{ number_format($summary['total_billed']) }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-body bg-white shadow-sm border-left-3 border-left-success py-3 mb-3">
            <span class="text-uppercase font-size-xs text-muted font-weight-semibold">Total Amount Paid</span>
            <h3 class="font-weight-bold mb-0 text-success">₦{{ number_format($summary['total_paid']) }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-body bg-white shadow-sm border-left-3 border-left-danger py-3 mb-3">
            <span class="text-uppercase font-size-xs text-muted font-weight-semibold">Remaining Balance</span>
            <h3 class="font-weight-bold mb-0 text-danger">₦{{ number_format($summary['total_balance']) }}</h3>
        </div>
    </div>
</div>

{{-- Invoiced Fee Records & Payment Action --}}
<div class="card shadow-sm">
    <div class="card-header bg-white header-elements-inline py-3 border-bottom">
        <h6 class="card-title font-weight-bold mb-0 text-dark">
            <i class="icon-file-spreadsheet mr-2 text-primary"></i> School Fee Records &amp; Payment History
        </h6>
        <div class="header-elements">
            @if($available_payments->count() > 0)
                <button type="button" class="btn btn-sm btn-outline-primary font-weight-semibold" data-toggle="modal" data-target="#billStudentModal">
                    <i class="icon-plus2 mr-1"></i> Add Fee / Bill Student
                </button>
            @endif
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered mb-0">
            <thead class="bg-light text-muted font-size-xs text-uppercase">
                <tr>
                    <th>Fee Title</th>
                    <th>Invoice Ref</th>
                    <th class="text-right">Amount Billed</th>
                    <th class="text-right text-success">Amount Paid</th>
                    <th class="text-right text-danger">Remaining Balance</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" style="width: 220px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($summary['records'] as $pr)
                    @php
                        $amt = $pr->payment ? $pr->payment->amount : 0;
                        $paid = $pr->amt_paid ?: 0;
                        $bal = $pr->balance !== null ? $pr->balance : ($amt - $paid);
                        $isPaid = $bal <= 0;
                    @endphp
                    <tr>
                        <td>
                            <div class="font-weight-bold text-dark">{{ $pr->payment->title ?? 'School Fees' }}</div>
                            <div class="small text-muted">{{ $pr->payment->description ?: 'Standard fee' }}</div>
                        </td>
                        <td class="font-weight-semibold text-primary">
                            <span class="badge badge-light border">{{ $pr->ref_no ?: '—' }}</span>
                        </td>
                        <td class="text-right font-weight-bold">
                            ₦{{ number_format($amt) }}
                        </td>
                        <td class="text-right font-weight-bold text-success">
                            ₦{{ number_format($paid) }}
                        </td>
                        <td class="text-right font-weight-bold text-danger font-size-base">
                            ₦{{ number_format(max(0, $bal)) }}
                        </td>
                        <td class="text-center">
                            @if($isPaid)
                                <span class="badge badge-success px-2 py-1"><i class="icon-checkmark mr-1"></i> Paid</span>
                            @elseif($paid > 0)
                                <span class="badge badge-warning px-2 py-1 text-dark"><i class="icon-pie5 mr-1"></i> Partial</span>
                            @else
                                <span class="badge badge-danger px-2 py-1"><i class="icon-cross2 mr-1"></i> Unpaid</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(!$isPaid)
                                <button type="button" class="btn btn-xs btn-success font-weight-semibold shadow-sm mr-1" data-toggle="modal" data-target="#recordPaymentModal-{{ $pr->id }}">
                                    <i class="icon-cash mr-1"></i> Record Payment
                                </button>
                            @endif

                            @if($pr->receipt->count() > 0)
                                <div class="btn-group">
                                    <button type="button" class="btn btn-xs btn-outline-primary dropdown-toggle" data-toggle="dropdown">
                                        <i class="icon-printer mr-1"></i> Receipts ({{ $pr->receipt->count() }})
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        @foreach($pr->receipt as $r)
                                            <a href="{{ route('accountant.receipts', $r->id) }}" class="dropdown-item" target="_blank">
                                                Receipt #{{ $r->id }} (₦{{ number_format($r->amt_paid) }}) &bull; {{ $r->created_at->format('M d') }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Record Payment Modal --}}
                            <div class="modal fade text-left" id="recordPaymentModal-{{ $pr->id }}" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('accountant.store_payment', $pr->id) }}">
                                            @csrf
                                            <input type="hidden" name="session" value="{{ $year }}">

                                            <div class="modal-header bg-success text-white">
                                                <h6 class="modal-title font-weight-bold">
                                                    <i class="icon-cash mr-1"></i> Record Fee Payment
                                                </h6>
                                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="bg-light p-2 rounded mb-3">
                                                    <strong>Fee:</strong> {{ $pr->payment->title ?? 'School Fees' }}<br>
                                                    <strong>Total Billed:</strong> ₦{{ number_format($amt) }}<br>
                                                    <strong>Previously Paid:</strong> ₦{{ number_format($paid) }}<br>
                                                    <strong class="text-danger">Current Balance:</strong> ₦{{ number_format(max(0, $bal)) }}
                                                </div>

                                                <div class="form-group">
                                                    <label class="font-weight-semibold">Amount Being Paid (₦): <span class="text-danger">*</span></label>
                                                    <input type="number" step="1" min="1" max="{{ max(1, $bal) }}" name="amt_paid" class="form-control font-weight-bold" value="{{ max(1, $bal) }}" required>
                                                    <span class="text-muted font-size-xs">Enter full balance or partial installment amount.</span>
                                                </div>

                                                <div class="form-group">
                                                    <label class="font-weight-semibold">Payment Method: <span class="text-danger">*</span></label>
                                                    <select name="method" class="form-control" required>
                                                        <option value="Cash">Cash</option>
                                                        <option value="Bank Transfer">Bank Transfer / POS</option>
                                                        <option value="Online Gateway">Online Payment Gateway</option>
                                                        <option value="Cheque / Draft">Bank Cheque / Draft</option>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label class="font-weight-semibold">Payment Reference / Teller No (Optional):</label>
                                                    <input type="text" name="ref_no" class="form-control" placeholder="e.g. TEL-8940294 or POS Reference">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success font-weight-semibold shadow-sm">
                                                    <i class="icon-checkmark mr-1"></i> Confirm &amp; Issue Receipt
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="icon-file-text2 icon-2x mb-2 text-muted"></i>
                            <p class="mb-0">No fee invoices assigned to this student for session {{ $year }}.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Fee Modal --}}
@if($available_payments->count() > 0)
    <div class="modal fade" id="billStudentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('accountant.bill_student') }}">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                    <input type="hidden" name="session" value="{{ $year }}">

                    <div class="modal-header bg-primary text-white">
                        <h6 class="modal-title font-weight-bold">
                            <i class="icon-plus2 mr-1"></i> Issue Invoice to {{ $student->name }}
                        </h6>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-semibold">Select Fee Structure:</label>
                            <select name="payment_id" class="form-control" required>
                                @foreach($available_payments as $ap)
                                    <option value="{{ $ap->id }}">
                                        {{ $ap->title }} — ₦{{ number_format($ap->amount) }} ({{ $ap->my_class ? $ap->my_class->name : 'All Classes' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary font-weight-semibold">
                            <i class="icon-check mr-1"></i> Issue Fee Invoice
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@endsection
