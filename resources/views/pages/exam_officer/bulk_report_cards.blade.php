<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bulk Report Cards - {{ $exam->name }}</title>
    
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">

    <style>
        body {
            background-color: #f8fafc;
            color: #1e293b;
            font-family: 'Roboto', sans-serif;
            font-size: 13px;
        }
        .report-card {
            max-width: 860px;
            margin: 20px auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 30px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .page-break {
            page-break-after: always;
            break-after: page;
        }
        .report-header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .school-title {
            font-size: 24px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .report-title {
            font-size: 16px;
            font-weight: 700;
            color: #2563eb;
            text-transform: uppercase;
            margin-bottom: 0;
        }
        .info-table td {
            padding: 4px 8px;
            border: none;
            font-size: 13px;
        }
        .scores-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            padding: 8px 6px;
            text-align: center;
        }
        .scores-table td {
            padding: 7px 6px;
            vertical-align: middle;
            font-size: 12px;
        }
        .summary-card {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 12px;
        }
        .remarks-table th, .remarks-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .report-card {
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
    <button onclick="window.print()" class="btn btn-primary font-weight-bold px-4 shadow-sm mr-2">
        <i class="icon-printer mr-1"></i> Print All Report Cards ({{ count($cards) }} Students)
    </button>
    <a href="{{ url()->previous() }}" class="btn btn-secondary px-3">Back</a>
</div>

@foreach($cards as $cIdx => $data)
    @if(isset($data['is_cleared']) && !$data['is_cleared'])
        {{-- Fee Locked Card Page --}}
        <div class="report-card {{ !$loop->last ? 'page-break' : '' }}">
            <div class="report-header">
                <div class="row align-items-center">
                    <div class="col-2 text-center">
                        <img src="{{ Usr::getTenantAwarePhoto(Qs::getSetting('logo') ?: 'global_assets/images/logo.png') }}" alt="School Logo" style="max-height: 80px; max-width: 80px;">
                    </div>
                    <div class="col-8 text-center">
                        <h2 class="school-title">{{ Qs::getSetting('system_name') ?: 'DIGISKUL ACADEMY' }}</h2>
                        <h5 class="report-title text-danger">REPORT CARD LOCKED — SCHOOL FEES NOT PAID</h5>
                        <span class="badge badge-dark px-3 py-1 font-weight-bold">{{ $exam->name }} &bull; {{ $year }}</span>
                    </div>
                    <div class="col-2 text-center">
                        <img src="{{ Usr::getTenantAwarePhoto($data['student']->photo) }}" alt="Student" class="rounded border" style="max-height: 80px; max-width: 80px; object-fit: cover;">
                    </div>
                </div>
            </div>

            <div class="bg-light p-3 rounded mb-3 border">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Student:</strong> {{ $data['student']->name }} ({{ $data['sr']->adm_no ?: $data['student']->username }})<br>
                        <strong>Class:</strong> {{ $data['sr']->my_class->name }} — {{ $data['sr']->section->name }}
                    </div>
                    <div class="col-md-6 text-right">
                        <strong>Total Outstanding Fees:</strong> <span class="text-danger font-weight-bold font-size-base">₦{{ number_format($data['fee_summary']['total_balance']) }}</span>
                    </div>
                </div>
            </div>

            <div class="alert alert-danger py-4 text-center">
                <h5 class="font-weight-bold mb-1">Academic Report Card Withheld</h5>
                <p class="mb-0 small">This report card is locked due to unpaid school fees. Please contact the Accounts Office for financial clearance.</p>
            </div>
        </div>
    @else
        <div class="report-card {{ !$loop->last ? 'page-break' : '' }}">
            {{-- Header --}}
            <div class="report-header">
                <div class="row align-items-center">
                    <div class="col-2 text-center">
                        <img src="{{ Usr::getTenantAwarePhoto(Qs::getSetting('logo') ?: 'global_assets/images/logo.png') }}" alt="School Logo" style="max-height: 80px; max-width: 80px;">
                    </div>
                    <div class="col-8 text-center">
                        <h2 class="school-title">{{ Qs::getSetting('system_name') ?: 'DIGISKUL ACADEMY' }}</h2>
                        <p class="mb-1 text-muted small">{{ Qs::getSetting('address') ?: 'Excellence in Education' }}</p>
                        <h5 class="report-title">STUDENT ACADEMIC PERFORMANCE REPORT</h5>
                        <span class="badge badge-dark px-3 py-1 font-weight-bold">{{ $exam->name }} &bull; {{ $year }}</span>
                    </div>
                    <div class="col-2 text-center">
                        <img src="{{ Usr::getTenantAwarePhoto($data['student']->photo) }}" alt="Student" class="rounded border" style="max-height: 80px; max-width: 80px; object-fit: cover;">
                    </div>
                </div>
            </div>

            {{-- Student Demographics Grid --}}
            <div class="bg-light p-3 rounded mb-3 border">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table info-table mb-0">
                            <tr>
                                <td class="font-weight-bold text-muted" style="width: 130px;">Student Name:</td>
                                <td class="font-weight-bold text-dark">{{ $data['student']->name }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold text-muted">Admission No:</td>
                                <td class="font-weight-bold text-primary">{{ $data['sr']->adm_no ?: ($data['student']->username ?: '—') }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold text-muted">Class &amp; Section:</td>
                                <td class="font-weight-bold">{{ $data['sr']->my_class->name }} — Section {{ $data['sr']->section->name }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table info-table mb-0">
                            <tr>
                                <td class="font-weight-bold text-muted" style="width: 130px;">Gender:</td>
                                <td>{{ $data['student']->gender ?: '—' }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold text-muted">Academic Session:</td>
                                <td>{{ $year }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold text-muted">Date of Birth:</td>
                                <td>{{ $data['student']->dob ? date('d M, Y', strtotime($data['student']->dob)) : '—' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

        {{-- Subject Scores --}}
        <div class="mb-3">
            <h6 class="font-weight-bold text-uppercase text-dark mb-2">Subject Performance &amp; Grades</h6>
            <table class="table table-bordered table-sm scores-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th style="text-align: left;">Subject</th>
                        <th style="width: 70px;">CA 1</th>
                        <th style="width: 70px;">CA 2</th>
                        <th style="width: 75px;">Exam</th>
                        <th style="width: 75px;">Total</th>
                        <th style="width: 65px;">Grade</th>
                        <th style="width: 70px;">Position</th>
                        <th style="width: 110px;">Remark</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['subject_rows'] as $sIdx => $row)
                        <tr>
                            <td class="text-center font-weight-bold text-muted">{{ $sIdx + 1 }}</td>
                            <td class="font-weight-bold text-dark">{{ $row['subject']->name }}</td>
                            <td class="text-center">{{ $row['ca1'] !== null ? $row['ca1'] : '—' }}</td>
                            <td class="text-center">{{ $row['ca2'] !== null ? $row['ca2'] : '—' }}</td>
                            <td class="text-center">{{ $row['exm'] !== null ? $row['exm'] : '—' }}</td>
                            <td class="text-center font-weight-bold text-primary">{{ $row['total'] !== null ? $row['total'] : '—' }}</td>
                            <td class="text-center font-weight-bold">
                                @if($row['grade'] && $row['grade'] !== '—')
                                    <span class="badge badge-light border">{{ $row['grade'] }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center font-weight-semibold">
                                {{ $row['sub_pos'] ? Mk::getSuffix($row['sub_pos']) : '—' }}
                            </td>
                            <td class="text-center small text-muted">{{ $row['remark'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-3">No subjects configured.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Overall Summary & Attendance --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="summary-card h-100">
                    <h6 class="font-weight-bold text-primary text-uppercase mb-2" style="font-size: 12px;">
                        <i class="icon-calculator mr-1"></i> Overall Academic Summary
                    </h6>
                    <table class="table info-table mb-0">
                        <tr>
                            <td class="font-weight-bold text-muted" style="width: 150px;">Total Marks:</td>
                            <td class="font-weight-bold font-size-base text-dark">
                                {{ $data['exam_record'] && $data['exam_record']->total !== null ? $data['exam_record']->total : $data['total_score_sum'] }}
                                <span class="text-muted font-size-xs font-weight-normal">/ {{ $data['subjects_count'] * 100 }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold text-muted">Overall Average:</td>
                            <td class="font-weight-bold font-size-base text-primary">
                                {{ $data['exam_record'] && $data['exam_record']->ave !== null ? $data['exam_record']->ave . '%' : $data['calculated_average'] . '%' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold text-muted">Class Position:</td>
                            <td class="font-weight-bold text-success">
                                @if($data['exam_record'] && $data['exam_record']->class_pos)
                                    {{ Mk::getSuffix($data['exam_record']->class_pos) }} <span class="text-muted font-size-xs font-weight-normal">(out of {{ $data['class_students_count'] }} students)</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold text-muted">Section Position:</td>
                            <td class="font-weight-semibold">
                                @if($data['exam_record'] && $data['exam_record']->pos)
                                    {{ Mk::getSuffix($data['exam_record']->pos) }} <span class="text-muted font-size-xs font-weight-normal">(out of {{ $data['section_students_count'] }} students)</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="col-md-6">
                <div class="summary-card h-100">
                    <h6 class="font-weight-bold text-success text-uppercase mb-2" style="font-size: 12px;">
                        <i class="icon-calendar52 mr-1"></i> Attendance Record (Phase 1)
                    </h6>
                    <table class="table info-table mb-0">
                        <tr>
                            <td class="font-weight-bold text-muted" style="width: 160px;">Total School Days:</td>
                            <td class="font-weight-bold text-dark">{{ $data['attendance_stats']['total_days'] }} Days</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold text-muted">Days Present:</td>
                            <td class="font-weight-bold text-success">{{ $data['attendance_stats']['present_days'] }} Days</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold text-muted">Days Absent:</td>
                            <td class="font-weight-bold text-danger">{{ $data['attendance_stats']['absent_days'] }} Days</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold text-muted">Attendance Rate:</td>
                            <td class="font-weight-bold text-primary">{{ $data['attendance_stats']['percentage'] }}%</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Remarks Block --}}
        <div class="mb-2">
            <h6 class="font-weight-bold text-uppercase text-dark mb-2">Remarks &amp; Approvals</h6>
            <table class="table remarks-table mb-0">
                <tbody>
                    <tr>
                        <th style="width: 180px; background-color: #f8fafc;" class="font-weight-bold">Class Teacher Remark</th>
                        <td>{{ $data['exam_record'] && $data['exam_record']->t_comment ? $data['exam_record']->t_comment : 'Satisfactory academic performance.' }}</td>
                        <td style="width: 140px; text-align: center;" class="text-muted small">Signature</td>
                    </tr>
                    <tr>
                        <th style="background-color: #f8fafc;" class="font-weight-bold">Exam Officer Remark</th>
                        <td>{{ $data['exam_record'] && $data['exam_record']->exam_officer_comment ? $data['exam_record']->exam_officer_comment : 'Verified and approved.' }}</td>
                        <td style="text-align: center;" class="text-muted small">Signature &amp; Stamp</td>
                    </tr>
                    <tr>
                        <th style="background-color: #f8fafc;" class="font-weight-bold">Principal Remark</th>
                        <td>{{ $data['exam_record'] && $data['exam_record']->p_comment ? $data['exam_record']->p_comment : 'Promoted / Recommended for advancement.' }}</td>
                        <td style="text-align: center;" class="text-muted small">Date: {{ date('d/m/Y') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endif
@endforeach

</body>
</html>
