<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Official Payment Receipt #{{ $receipt->id }} - {{ $student->name }}</title>
    
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">

    <style>
        body {
            background-color: #f8fafc;
            color: #1e293b;
            font-family: 'Roboto', sans-serif;
            font-size: 13px;
        }
        .receipt-container {
            max-width: 650px;
            margin: 20px auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 30px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .receipt-header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .school-title {
            font-size: 20px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .receipt-title {
            font-size: 15px;
            font-weight: 700;
            color: #16a34a;
            text-transform: uppercase;
            margin-bottom: 0;
        }
        .info-table td {
            padding: 4px 6px;
            border: none;
            font-size: 13px;
        }
        .details-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 11px;
            text-transform: uppercase;
            padding: 8px 6px;
        }
        .details-table td {
            padding: 8px 6px;
            font-size: 13px;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .receipt-container {
                border: none;
                box-shadow: none;
                margin: 0 auto;
                padding: 10px;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

{{-- Print Bar --}}
<div class="container text-center my-3 no-print">
    <button onclick="window.print()" class="btn btn-success font-weight-bold px-4 shadow-sm mr-2">
        <i class="icon-printer mr-1"></i> Print Receipt
    </button>
    <a href="{{ url()->previous() }}" class="btn btn-secondary px-3">Back</a>
</div>

<div class="receipt-container">
    {{-- Header --}}
    <div class="receipt-header text-center">
        <div class="row align-items-center mb-2">
            <div class="col-2 text-left">
                <img src="{{ Usr::getTenantAwarePhoto(Qs::getSetting('logo') ?: 'global_assets/images/logo.png') }}" alt="Logo" style="max-height: 65px; max-width: 65px;">
            </div>
            <div class="col-8">
                <h2 class="school-title">{{ Qs::getSetting('system_name') ?: 'DIGISKUL ACADEMY' }}</h2>
                <p class="mb-1 text-muted small">{{ Qs::getSetting('address') ?: 'Official Bursary & Accounts Receipt' }}</p>
                <h5 class="receipt-title">OFFICIAL PAYMENT RECEIPT</h5>
            </div>
            <div class="col-2 text-right">
                <div class="badge badge-success px-2 py-1 font-weight-bold">PAID</div>
            </div>
        </div>
    </div>

    {{-- Transaction & Student Info --}}
    <div class="bg-light p-3 rounded mb-3 border">
        <div class="row">
            <div class="col-6">
                <table class="table info-table mb-0">
                    <tr>
                        <td class="font-weight-bold text-muted" style="width: 110px;">Receipt No:</td>
                        <td class="font-weight-bold text-primary">#{{ str_pad($receipt->id, 6, '0', STR_PAD_LEFT) }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold text-muted">Student Name:</td>
                        <td class="font-weight-bold text-dark">{{ $student->name }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold text-muted">Admission No:</td>
                        <td class="font-weight-semibold">{{ $sr ? ($sr->adm_no ?: $student->username) : $student->username }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-6">
                <table class="table info-table mb-0">
                    <tr>
                        <td class="font-weight-bold text-muted" style="width: 120px;">Payment Date:</td>
                        <td>{{ $receipt->created_at->format('d M, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold text-muted">Class / Session:</td>
                        <td>{{ $sr && $sr->my_class ? $sr->my_class->name . ' (' . $sr->section->name . ')' : '—' }} &bull; {{ $year }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold text-muted">Reference No:</td>
                        <td>{{ $pr->ref_no ?: '—' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Payment Details Table --}}
    <div class="mb-4">
        <table class="table table-bordered details-table mb-0">
            <thead>
                <tr>
                    <th>Description / Fee Item</th>
                    <th class="text-right" style="width: 120px;">Total Billed</th>
                    <th class="text-right text-success" style="width: 130px;">Amount Paid</th>
                    <th class="text-right text-danger" style="width: 130px;">Remaining Balance</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="font-weight-bold text-dark">
                        {{ $payment->title ?? 'School Fees' }}
                        <div class="small text-muted font-weight-normal">{{ $payment->description ?: 'Payment installment' }}</div>
                    </td>
                    <td class="text-right font-weight-semibold">₦{{ number_format($payment ? $payment->amount : 0) }}</td>
                    <td class="text-right font-weight-bold text-success font-size-base">₦{{ number_format($receipt->amt_paid) }}</td>
                    <td class="text-right font-weight-bold text-danger">₦{{ number_format($receipt->balance) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Signatures & Stamp --}}
    <div class="row pt-4 border-top">
        <div class="col-6 text-center">
            <div style="height: 35px;"></div>
            <div class="border-top pt-1 small font-weight-semibold text-muted">
                Received By (Bursar / Accountant)
            </div>
        </div>
        <div class="col-6 text-center">
            <div style="height: 35px;"></div>
            <div class="border-top pt-1 small font-weight-semibold text-muted">
                Official School Stamp &amp; Date
            </div>
        </div>
    </div>
</div>

</body>
</html>
