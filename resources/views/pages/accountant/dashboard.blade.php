@extends('layouts.master')
@section('page_title', 'Accountant Workspace & Financial Hub')
@section('content')

{{-- Welcome Header --}}
<div class="card bg-slate-900 text-white shadow-sm border-0 mb-4" style="background: linear-gradient(135deg, #064e3b 0%, #022c22 100%);">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h4 class="font-weight-bold mb-1 text-white">
                    <i class="icon-cash3 mr-2 text-success"></i> Accountant Financial Management Hub
                </h4>
                <p class="mb-0 text-emerald-200">
                    Manage student fee structures, record payments, generate instant receipts, track debts, and dispatch automated SMS reminders.
                </p>
            </div>
            <div class="mt-2 mt-md-0">
                <span class="badge badge-success px-3 py-2 font-size-sm">Session: {{ $year }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Top Financial Analytics KPI Cards --}}
<div class="row">
    <div class="col-sm-6 col-xl-3">
        <div class="card card-body bg-white shadow-sm border-left-3 border-left-primary">
            <div class="media">
                <div class="media-body">
                    <h3 class="font-weight-bold mb-0 text-primary">₦{{ number_format($analytics['total_billed']) }}</h3>
                    <span class="text-uppercase font-size-xs text-muted font-weight-semibold">Total Expected Fees</span>
                </div>
                <div class="ml-3 align-self-center">
                    <i class="icon-calculator icon-2x text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card card-body bg-white shadow-sm border-left-3 border-left-success">
            <div class="media">
                <div class="media-body">
                    <h3 class="font-weight-bold mb-0 text-success">₦{{ number_format($analytics['total_collected']) }}</h3>
                    <span class="text-uppercase font-size-xs text-muted font-weight-semibold">Total Collected ({{ $analytics['collection_rate'] }}%)</span>
                </div>
                <div class="ml-3 align-self-center">
                    <i class="icon-checkmark-circle icon-2x text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card card-body bg-white shadow-sm border-left-3 border-left-danger">
            <div class="media">
                <div class="media-body">
                    <h3 class="font-weight-bold mb-0 text-danger">₦{{ number_format($analytics['total_outstanding']) }}</h3>
                    <span class="text-uppercase font-size-xs text-muted font-weight-semibold">Total Outstanding</span>
                </div>
                <div class="ml-3 align-self-center">
                    <i class="icon-warning2 icon-2x text-danger"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card card-body bg-white shadow-sm border-left-3 border-left-warning">
            <div class="media">
                <div class="media-body">
                    <h3 class="font-weight-bold mb-0 text-warning">{{ $analytics['count_not_paid'] + $analytics['count_partially_paid'] }}</h3>
                    <span class="text-uppercase font-size-xs text-muted font-weight-semibold">Students with Debt</span>
                </div>
                <div class="ml-3 align-self-center">
                    <i class="icon-users4 icon-2x text-warning"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Action Hub --}}
<div class="row">
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white font-weight-bold py-3 border-bottom text-primary">
                <i class="icon-users4 mr-2"></i> Student Directory
            </div>
            <div class="card-body d-flex flex-column justify-content-between">
                <p class="text-muted small">
                    Search students, view guardian details, check fee payment status, and record payments.
                </p>
                <a href="{{ route('accountant.students') }}" class="btn btn-primary btn-block font-weight-semibold shadow-sm">
                    <i class="icon-search4 mr-1"></i> Search Students
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white font-weight-bold py-3 border-bottom text-info">
                <i class="icon-file-text2 mr-2"></i> Fee Invoicing
            </div>
            <div class="card-body d-flex flex-column justify-content-between">
                <p class="text-muted small">
                    Create school-fee invoices, set up termly tuition, and bill classes or individual students.
                </p>
                <a href="{{ route('accountant.invoices') }}" class="btn btn-info btn-block font-weight-semibold shadow-sm text-white">
                    <i class="icon-plus2 mr-1"></i> Manage Invoices
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white font-weight-bold py-3 border-bottom text-success">
                <i class="icon-stats-growth mr-2"></i> Financial Analytics
            </div>
            <div class="card-body d-flex flex-column justify-content-between">
                <p class="text-muted small">
                    View collection reports, payment breakdown by class &amp; section, and export fee audits.
                </p>
                <a href="{{ route('accountant.analytics') }}" class="btn btn-success btn-block font-weight-semibold shadow-sm">
                    <i class="icon-chart mr-1"></i> View Analytics
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white font-weight-bold py-3 border-bottom text-danger">
                <i class="icon-mobile mr-2"></i> Bulk SMS Reminders
            </div>
            <div class="card-body d-flex flex-column justify-content-between">
                <p class="text-muted small">
                    Identify debtors, auto-populate guardian phone numbers, and send bulk fee reminders.
                </p>
                <a href="{{ route('accountant.sms_reminders') }}" class="btn btn-danger btn-block font-weight-semibold shadow-sm">
                    <i class="icon-paperplane mr-1"></i> Send Fee Reminders
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Recent Transactions & Debtors Overview --}}
<div class="row mt-4">
    {{-- Recent Receipts --}}
    <div class="col-md-7">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white header-elements-inline py-3 border-bottom">
                <h6 class="card-title font-weight-bold mb-0 text-dark">
                    <i class="icon-history mr-2 text-primary"></i> Recent Payment Receipts
                </h6>
                <a href="{{ route('accountant.students') }}" class="btn btn-xs btn-light border">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 text-nowrap">
                    <thead class="bg-light text-muted font-size-xs text-uppercase">
                        <tr>
                            <th>Receipt #</th>
                            <th>Student</th>
                            <th>Fee Type</th>
                            <th class="text-right">Amount Paid</th>
                            <th class="text-center">Date</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent_receipts as $r)
                            <tr>
                                <td class="font-weight-bold text-primary">#{{ $r->id }}</td>
                                <td>
                                    <span class="font-weight-semibold text-dark">{{ $r->pr->student->name ?? 'N/A' }}</span>
                                </td>
                                <td>{{ $r->pr->payment->title ?? 'School Fees' }}</td>
                                <td class="text-right font-weight-bold text-success">₦{{ number_format($r->amt_paid) }}</td>
                                <td class="text-center small text-muted">{{ $r->created_at->format('M d, H:i') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('accountant.receipts', $r->id) }}" class="btn btn-xs btn-outline-primary" target="_blank">
                                        <i class="icon-printer mr-1"></i> Print
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No recent payments recorded for session {{ $year }}.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Outstanding Debtors Sample --}}
    <div class="col-md-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white header-elements-inline py-3 border-bottom">
                <h6 class="card-title font-weight-bold mb-0 text-danger">
                    <i class="icon-warning2 mr-2"></i> Debtors Sample
                </h6>
                <a href="{{ route('accountant.sms_reminders') }}" class="btn btn-xs btn-danger">Bulk Remind &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light text-muted font-size-xs text-uppercase">
                        <tr>
                            <th>Student</th>
                            <th>Class</th>
                            <th class="text-right">Outstanding</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($debtors_sample as $dItem)
                            <tr>
                                <td>
                                    <div class="font-weight-semibold text-dark">{{ $dItem['student']->name ?? 'N/A' }}</div>
                                    <div class="small text-muted">{{ $dItem['guardian_name'] }}: {{ $dItem['guardian_phone'] ?: 'No phone' }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-light border">{{ $dItem['my_class']->name ?? '—' }}</span>
                                </td>
                                <td class="text-right font-weight-bold text-danger">
                                    ₦{{ number_format($dItem['total_balance']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-success py-4">
                                    <i class="icon-checkmark-circle icon-2x mb-1 text-success"></i>
                                    <p class="mb-0 font-weight-semibold">No outstanding debtors for session {{ $year }}.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
