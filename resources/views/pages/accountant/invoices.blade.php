@extends('layouts.master')
@section('page_title', 'School Fee Invoices & Billing')
@section('content')

<div class="row">
    {{-- Create Fee / Invoice Structure --}}
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white font-weight-bold py-3 border-bottom text-primary">
                <i class="icon-plus-circle2 mr-2"></i> Create Fee Structure / Invoice
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('accountant.store_invoice') }}">
                    @csrf
                    <input type="hidden" name="year" value="{{ $year }}">

                    <div class="form-group">
                        <label class="font-weight-semibold">Fee Title / Name: <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. 1st Term School Tuition Fees" required>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Amount (₦): <span class="text-danger">*</span></label>
                        <input type="number" step="100" min="1" name="amount" class="form-control" placeholder="e.g. 45000" required>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Target Class: <span class="text-muted small">(Leave empty for All Classes)</span></label>
                        <select name="my_class_id" class="form-control">
                            <option value="">All Classes (General Fee)</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <span class="text-muted font-size-xs">Selecting a class will automatically generate invoices for all enrolled students.</span>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Description / Notes:</label>
                        <textarea name="description" rows="2" class="form-control" placeholder="e.g. Mandatory term tuition & development levy..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block font-weight-semibold shadow-sm">
                        <i class="icon-floppy-disk mr-1"></i> Save &amp; Generate Invoices
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Existing Fees & Billing Actions --}}
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white header-elements-inline py-3 border-bottom">
                <div>
                    <h5 class="card-title font-weight-bold mb-0 text-dark">
                        <i class="icon-file-text2 mr-2 text-primary"></i> Fee Structures for Session {{ $year }}
                    </h5>
                    <span class="text-muted small">Manage fee rates, assign invoices to classes, and review structures.</span>
                </div>
                <div class="header-elements">
                    <a href="{{ route('accountant.dashboard') }}" class="btn btn-sm btn-light border mr-2">
                        <i class="icon-arrow-left52 mr-1"></i> Dashboard
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered mb-0">
                    <thead class="bg-light text-muted font-size-xs text-uppercase">
                        <tr>
                            <th>#</th>
                            <th>Fee Title</th>
                            <th>Class Scope</th>
                            <th class="text-right">Amount</th>
                            <th class="text-center" style="width: 160px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $idx => $p)
                            <tr>
                                <td class="text-muted">{{ $idx + 1 }}</td>
                                <td>
                                    <div class="font-weight-bold text-dark">{{ $p->title }}</div>
                                    <div class="small text-muted">{{ $p->description ?: 'No description' }} &bull; Ref: {{ $p->ref_no }}</div>
                                </td>
                                <td>
                                    @if($p->my_class)
                                        <span class="badge badge-primary">{{ $p->my_class->name }}</span>
                                    @else
                                        <span class="badge badge-secondary">All Classes</span>
                                    @endif
                                </td>
                                <td class="text-right font-weight-bold text-success font-size-base">
                                    ₦{{ number_format($p->amount) }}
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-xs btn-outline-primary font-weight-semibold shadow-sm" data-toggle="modal" data-target="#billClassModal-{{ $p->id }}">
                                        <i class="icon-users mr-1"></i> Bill Class
                                    </button>

                                    {{-- Bill Class Modal --}}
                                    <div class="modal fade text-left" id="billClassModal-{{ $p->id }}" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('accountant.bill_class') }}">
                                                    @csrf
                                                    <input type="hidden" name="payment_id" value="{{ $p->id }}">
                                                    <input type="hidden" name="session" value="{{ $year }}">

                                                    <div class="modal-header bg-primary text-white">
                                                        <h6 class="modal-title font-weight-bold">
                                                            <i class="icon-plus2 mr-1"></i> Issue Invoices: {{ $p->title }}
                                                        </h6>
                                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="text-muted small">
                                                            Select a class to generate invoices of <strong>₦{{ number_format($p->amount) }}</strong> for all active students in that class for session <strong>{{ $year }}</strong>.
                                                        </p>
                                                        <div class="form-group">
                                                            <label class="font-weight-semibold">Select Class:</label>
                                                            <select name="class_id" class="form-control" required>
                                                                <option value="">Choose Class...</option>
                                                                @foreach($classes as $c)
                                                                    <option value="{{ $c->id }}" {{ $p->my_class_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary font-weight-semibold">
                                                            <i class="icon-check mr-1"></i> Generate Class Invoices
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
                                <td colspan="5" class="text-center text-muted py-4">
                                    No fee structures configured for session {{ $year }}. Create one using the form on the left.
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
