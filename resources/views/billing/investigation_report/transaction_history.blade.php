@extends('adminlte::page')

@section('title', 'Investigation Transaction History')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="m-0" style="font-size:18px; font-weight:600;">
            <i class="fas fa-history text-primary mr-2"></i>Investigation Transaction History
        </h1>
        <a href="{{ route('billing.investigation-report.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-chart-bar mr-1"></i>Revenue Report
        </a>
    </div>
@stop

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('billing.investigation-report.history') }}" class="row align-items-end">
                <div class="col-md-2 mb-2"><label class="small mb-1">From</label><input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-control"></div>
                <div class="col-md-2 mb-2"><label class="small mb-1">To</label><input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-control"></div>
                <div class="col-md-2 mb-2"><label class="small mb-1">Type</label><select name="type" class="form-control"><option value="">All</option><option value="cash" @selected($filters['type'] === 'cash')>Cash</option><option value="discount" @selected($filters['type'] === 'discount')>Discount</option></select></div>
                <div class="col-md-2 mb-2"><label class="small mb-1">Patient / Invoice</label><input type="search" name="search" value="{{ $filters['search'] }}" class="form-control" placeholder="Name, phone, code or bill no."></div>
                <div class="col-md-1 mb-2"><label class="small mb-1 d-block">&nbsp;</label><label class="mb-0"><input type="checkbox" name="due_only" value="1" @checked($filters['due_only'])> Due only</label></div>
                <div class="col-md-1 mb-2"><button class="btn btn-primary w-100">Filter</button></div>
                <div class="col-md-2 mb-2"><a href="{{ route('billing.investigation-report.history') }}" class="btn btn-outline-secondary w-100">Reset</a></div>
            </form>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6"><div class="small text-muted">Cash Collected</div><h4 class="mb-0 text-success">৳ {{ number_format((float) ($summary->cash ?? 0), 2) }}</h4></div>
        <div class="col-md-6"><div class="small text-muted">Discount</div><h4 class="mb-0 text-danger">৳ {{ number_format((float) ($summary->discount ?? 0), 2) }}</h4></div>
    </div>

    @if($filters['due_only'])
        <div class="card mb-3"><div class="card-header py-2"><strong>Due Collection by Test / Service</strong><span class="text-muted small ml-2">Cash collected from due payments only</span></div>
            <div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead><tr><th>Category</th><th>Test / Service</th><th class="text-right">Collected</th></tr></thead><tbody>
                @forelse($dueCollectionByService as $row)<tr><td>{{ $row->category }}</td><td>{{ $row->service }}</td><td class="text-right">৳ {{ number_format($row->collected, 2) }}</td></tr>
                @empty<tr><td colspan="3" class="text-center text-muted py-3">No due collection found.</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endif

    <div class="card"><div class="table-responsive"><table class="table table-sm table-hover mb-0">
        <thead><tr><th>Date</th><th>Invoice</th><th>Patient</th><th>Type</th><th>Method</th><th>Collected By</th><th class="text-right">Amount</th></tr></thead>
        <tbody>@forelse($transactions as $row)<tr>
            <td>{{ \Carbon\Carbon::parse($row->transaction_date)->format('d M Y') }}</td><td>{{ $row->BillNo }}</td><td>{{ $row->PatientName }} <small class="text-muted">{{ $row->PatientCode }}</small></td>
            @php
                $isDuePayment = in_array($row->source, ['paynow', 'paynow_bulk', 'paynow_discount', 'due_adjustment', 'due_adjustment_reversal'], true);
                $typeLabel = $row->type === 'cash'
                    ? ($isDuePayment ? 'Due Payment' : 'Cash')
                    : ($isDuePayment ? 'Due Discount' : 'Discount');
                $badgeClass = $row->type === 'cash' ? 'success' : 'warning';
            @endphp
            <td>
                <span class="badge badge-{{ $badgeClass }}">{{ $typeLabel }}</span>
                @if($isDuePayment)
                    <span class="badge badge-light border ml-1">Due</span>
                @endif
            </td><td>{{ $row->payment_method ?: '—' }}</td><td>{{ $row->collected_by ?: '—' }}</td><td class="text-right">৳ {{ number_format($row->amount, 2) }}</td>
        </tr>@empty<tr><td colspan="7" class="text-center text-muted py-4">No transaction found.</td></tr>@endforelse</tbody>
    </table></div><div class="card-footer">{{ $transactions->links() }}</div></div>
@stop
