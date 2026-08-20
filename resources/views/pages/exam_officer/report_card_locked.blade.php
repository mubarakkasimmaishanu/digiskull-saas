<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Report Card Locked - {{ $student->name }}</title>
    
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">

    <style>
        body {
            background-color: #f1f5f9;
            color: #1e293b;
            font-family: 'Roboto', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .locked-card {
            max-width: 680px;
            width: 100%;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .locked-header {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: #ffffff;
            padding: 30px 24px;
            text-align: center;
        }
        .lock-icon-circle {
            width: 70px;
            height: 70px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin-bottom: 15px;
        }
        .fee-table th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 12px;
            text-transform: uppercase;
        }
        .fee-table td {
            font-size: 13px;
            vertical-align: middle;
        }
    </style>
</head>
<body>

<div class="locked-card">
    <div class="locked-header">
        <div class="lock-icon-circle">
            🔒
        </div>
        <h3 class="font-weight-bold mb-1">Report Card Locked — School Fees Not Paid</h3>
        <p class="mb-0 text-white-50 small font-weight-semibold">
            Financial clearance required for academic report card generation.
        </p>
    </div>

    <div class="p-4">
        {{-- Student Summary --}}
        <div class="bg-light p-3 rounded mb-3 border">
            <div class="row">
                <div class="col-sm-6">
                    <span class="text-muted small">Student Name:</span>
                    <h6 class="font-weight-bold mb-1 text-dark">{{ $student->name }}</h6>
                    <span class="text-muted small">Admission No:</span>
                    <div class="font-weight-semibold text-primary">{{ $sr->adm_no ?: ($student->username ?: '—') }}</div>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted small">Class &amp; Section:</span>
                    <h6 class="font-weight-bold mb-1">{{ $sr->my_class->name }} — {{ $sr->section->name }}</h6>
                    <span class="text-muted small">Academic Session:</span>
                    <div class="font-weight-semibold">{{ $year }}</div>
                </div>
            </div>
        </div>

        {{-- Outstanding Invoices Table --}}
        <h6 class="font-weight-bold text-dark mb-2">Outstanding Fee Records</h6>
        <div class="table-responsive mb-3">
            <table class="table table-bordered table-sm fee-table mb-0">
                <thead>
                    <tr>
                        <th>Fee Title</th>
                        <th class="text-right">Billed</th>
                        <th class="text-right">Paid</th>
                        <th class="text-right text-danger">Outstanding Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fee_summary['records'] as $pr)
                        @php
                            $amt = $pr->payment ? $pr->payment->amount : 0;
                            $paid = $pr->amt_paid ?: 0;
                            $bal = $pr->balance !== null ? $pr->balance : ($amt - $paid);
                        @endphp
                        <tr>
                            <td class="font-weight-semibold">{{ $pr->payment->title ?? 'School Fees' }}</td>
                            <td class="text-right">₦{{ number_format($amt) }}</td>
                            <td class="text-right text-success">₦{{ number_format($paid) }}</td>
                            <td class="text-right font-weight-bold text-danger">₦{{ number_format(max(0, $bal)) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">No fee records found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-light font-weight-bold">
                        <td colspan="3" class="text-right text-uppercase">Total Outstanding:</td>
                        <td class="text-right text-danger font-size-base">₦{{ number_format($fee_summary['total_balance']) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="alert alert-warning border-0 small mb-4">
            <strong>Notice for Examination Officers &amp; Guardians:</strong>
            Under school academic policy, final term reports remain locked until all required session fee obligations are settled.
            Once the accountant records payment, this report card will unlock automatically.
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ url()->previous() }}" class="btn btn-light border px-4 font-weight-semibold">
                &larr; Go Back
            </a>
            @if(Qs::userIsTeamAccount())
                <a href="{{ route('accountant.student_fees', $student->id) }}" class="btn btn-danger font-weight-semibold shadow-sm">
                    Record Payment in Accounts &rarr;
                </a>
            @endif
        </div>
    </div>
</div>

</body>
</html>
