@extends('layouts.master')
@section('page_title', 'Financial Analytics & Revenue Reports')
@section('content')

<div class="card shadow-sm">
    <div class="card-header bg-white header-elements-inline py-3 border-bottom">
        <div>
            <h5 class="card-title font-weight-bold mb-1 text-primary">
                <i class="icon-stats-growth mr-2"></i> Financial Analytics &amp; Revenue Reports
            </h5>
            <span class="text-muted small">
                Comprehensive breakdown of expected fees, revenue collections, and outstanding debt by session, class, and section.
            </span>
        </div>
        <div class="header-elements">
            <a href="{{ route('accountant.dashboard') }}" class="btn btn-sm btn-light border mr-2">
                <i class="icon-arrow-left52 mr-1"></i> Dashboard
            </a>
            <button onclick="window.print()" class="btn btn-sm btn-outline-primary font-weight-semibold">
                <i class="icon-printer mr-1"></i> Print Report
            </button>
        </div>
    </div>

    {{-- Filter by Session & Class --}}
    <div class="card-body bg-light border-bottom py-3">
        <form method="GET" action="{{ route('accountant.analytics') }}">
            <div class="row align-items-end">
                <div class="col-md-4 mb-2">
                    <label class="font-weight-semibold font-size-xs text-muted text-uppercase mb-1">Academic Session</label>
                    <input type="text" name="session" class="form-control form-control-sm" value="{{ $year }}">
                </div>

                <div class="col-md-4 mb-2">
                    <label class="font-weight-semibold font-size-xs text-muted text-uppercase mb-1">Class Filter (Optional)</label>
                    <select name="class_id" class="form-control form-control-sm">
                        <option value="">All Classes</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $selected_class == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 mb-2 d-flex">
                    <button type="submit" class="btn btn-sm btn-primary font-weight-semibold mr-2 flex-grow-1">
                        <i class="icon-filter3 mr-1"></i> Generate Analytics
                    </button>
                    <a href="{{ route('accountant.analytics') }}" class="btn btn-sm btn-light border">Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- KPI Cards --}}
    <div class="card-body py-3 border-bottom">
        <div class="row">
            <div class="col-md-3 mb-2">
                <div class="p-3 border rounded bg-white text-center">
                    <span class="text-uppercase font-size-xs text-muted font-weight-semibold">Total Billed</span>
                    <h4 class="font-weight-bold mb-0 text-primary">₦{{ number_format($analytics['total_billed']) }}</h4>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="p-3 border rounded bg-white text-center">
                    <span class="text-uppercase font-size-xs text-muted font-weight-semibold">Total Collected ({{ $analytics['collection_rate'] }}%)</span>
                    <h4 class="font-weight-bold mb-0 text-success">₦{{ number_format($analytics['total_collected']) }}</h4>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="p-3 border rounded bg-white text-center">
                    <span class="text-uppercase font-size-xs text-muted font-weight-semibold">Total Outstanding</span>
                    <h4 class="font-weight-bold mb-0 text-danger">₦{{ number_format($analytics['total_outstanding']) }}</h4>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="p-3 border rounded bg-white text-center">
                    <span class="text-uppercase font-size-xs text-muted font-weight-semibold">Fully Paid / Debtors</span>
                    <h4 class="font-weight-bold mb-0 text-dark">
                        <span class="text-success">{{ $analytics['count_paid'] }}</span> / 
                        <span class="text-danger">{{ $analytics['count_not_paid'] + $analytics['count_partially_paid'] }}</span>
                    </h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Class Breakdown Table --}}
    <div class="card-body">
        <h6 class="font-weight-bold text-dark mb-3">
            <i class="icon-pie-chart mr-1 text-primary"></i> Class-Level Collection &amp; Debt Matrix
        </h6>

        <div class="table-responsive mb-4">
            <table class="table table-hover table-striped table-bordered mb-0">
                <thead class="bg-slate-800 text-white font-size-xs text-uppercase">
                    <tr>
                        <th>Class</th>
                        <th class="text-center">Students</th>
                        <th class="text-right">Expected Billed</th>
                        <th class="text-right text-success">Total Collected</th>
                        <th class="text-right text-danger">Outstanding Balance</th>
                        <th class="text-center">Paid Count</th>
                        <th class="text-center">Partial Count</th>
                        <th class="text-center">Unpaid Count</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($analytics['class_breakdown'] as $cb)
                        <tr>
                            <td class="font-weight-bold text-primary">{{ $cb['class']->name }}</td>
                            <td class="text-center font-weight-semibold">{{ $cb['total_students'] }}</td>
                            <td class="text-right font-weight-semibold">₦{{ number_format($cb['total_billed']) }}</td>
                            <td class="text-right font-weight-bold text-success">₦{{ number_format($cb['total_collected']) }}</td>
                            <td class="text-right font-weight-bold text-danger">₦{{ number_format($cb['total_outstanding']) }}</td>
                            <td class="text-center"><span class="badge badge-success">{{ $cb['paid_count'] }}</span></td>
                            <td class="text-center"><span class="badge badge-warning text-dark">{{ $cb['partial_count'] }}</span></td>
                            <td class="text-center"><span class="badge badge-danger">{{ $cb['unpaid_count'] }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No class financial records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Section-Level Detailed Breakdown --}}
        <h6 class="font-weight-bold text-dark mb-3">
            <i class="icon-tree6 mr-1 text-primary"></i> Section-Level Granular Breakdown
        </h6>

        @foreach($analytics['class_breakdown'] as $cb)
            @if(count($cb['sections']) > 0)
                <div class="card border mb-3 shadow-none">
                    <div class="card-header bg-light py-2 header-elements-inline">
                        <span class="font-weight-bold text-dark">{{ $cb['class']->name }} Sections</span>
                        <span class="text-muted small">Total Outstanding: <strong class="text-danger">₦{{ number_format($cb['total_outstanding']) }}</strong></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="bg-light font-size-xs text-muted text-uppercase">
                                <tr>
                                    <th>Section</th>
                                    <th class="text-center">Enrolled</th>
                                    <th class="text-right">Billed</th>
                                    <th class="text-right text-success">Collected</th>
                                    <th class="text-right text-danger">Outstanding</th>
                                    <th class="text-center">Status (Paid / Partial / Unpaid)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cb['sections'] as $secItem)
                                    <tr>
                                        <td class="font-weight-semibold">Section {{ $secItem['section']->name }}</td>
                                        <td class="text-center">{{ $secItem['total_students'] }}</td>
                                        <td class="text-right">₦{{ number_format($secItem['total_billed']) }}</td>
                                        <td class="text-right text-success font-weight-semibold">₦{{ number_format($secItem['total_collected']) }}</td>
                                        <td class="text-right text-danger font-weight-bold">₦{{ number_format($secItem['total_outstanding']) }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-success mr-1">{{ $secItem['paid_count'] }} Paid</span>
                                            <span class="badge badge-warning text-dark mr-1">{{ $secItem['partial_count'] }} Partial</span>
                                            <span class="badge badge-danger">{{ $secItem['unpaid_count'] }} Unpaid</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>

@endsection
