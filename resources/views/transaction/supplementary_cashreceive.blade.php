@extends('adminlte::page')

@section('title', 'Cash Receive Report')

@section('content_header')
    <h1><i class="fas fa-file-invoice-dollar mr-2"></i>Cash Receive Report</h1>
@stop

@section('content')
    <div class="card card-outline card-success">
        <div class="card-body">
            <form method="GET" class="form-row align-items-end mb-3">
                <div class="col-md-3 mb-2">
                    <label>From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-3 mb-2">
                    <label>To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-5 mb-2 d-flex align-items-center" style="gap:8px; white-space:nowrap;">
                    <button class="btn btn-success"><i class="fas fa-search mr-1"></i> Show Report</button>
                    <a href="{{ route('supplementary.cashreceive') }}" class="btn btn-outline-secondary">Reset</a>
                    <a href="{{ route('supplementary.cashreceive.pdf', ['from_date' => $fromDate, 'to_date' => $toDate]) }}" class="btn btn-outline-danger"><i class="fas fa-file-pdf mr-1"></i> Export PDF</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Date</th><th>Receipt No.</th><th>Ref No.</th><th>Receive From</th><th>Description</th><th class="text-right">Amount</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receipts as $receipt)
                            <tr>
                                <td>{{ $receipt->posting_date ? date('d-m-Y', strtotime($receipt->posting_date)) : '-' }}</td>
                                <td>{{ $receipt->receipt_number ?: '-' }}</td>
                                <td>{{ $receipt->ref_number }}</td>
                                <td>{{ $receipt->account_title ?: '-' }}</td>
                                <td>{{ $receipt->particular ?: '-' }}</td>
                                <td class="text-right font-weight-bold">{{ number_format((float) $receipt->amount, 2) }}</td>
                                <td><span class="badge badge-{{ $receipt->status === 'Posted' ? 'success' : 'warning' }}">{{ $receipt->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No cash receive record found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($receipts->hasPages())
            <div class="card-footer">{{ $receipts->links() }}</div>
        @endif
    </div>
@stop
