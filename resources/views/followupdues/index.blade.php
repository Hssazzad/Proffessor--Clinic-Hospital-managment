@extends('adminlte::page')

@section('title', 'Follow-up Dues')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<style>
    .badge-pending { background: #ffc107; color: #212529; }
    .badge-paid   { background: #28a745; color: #fff; }
    .badge-waived { background: #dc3545; color: #fff; }
    .filter-bar { display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 20px; }
    .filter-bar .form-group { margin-bottom: 0; }
    .filter-bar label { font-size: 13px; margin-bottom: 2px; }
</style>
@endpush

@section('content_header')
    <h1 class="text-primary font-weight-bold">Follow-up Due Tracker</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif

<div class="card card-outline card-primary shadow">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-hand-holding-usd mr-2"></i>Follow-up Dues</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="filter-bar">
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control form-control-sm" style="min-width:140px;">
                    <option value="">All</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid"    {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="waived"  {{ request('status') === 'waived' ? 'selected' : '' }}>Waived</option>
                </select>
            </div>
            <div class="form-group">
                <label>From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="form-group">
                <label>To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Filter</button>
                <a href="{{ route('followupdues.index') }}" class="btn btn-sm btn-default"><i class="fas fa-times"></i> Clear</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-sm" id="duesTable">
                <thead class="bg-light">
                    <tr>
                        <th>#</th>
                        <th>Patient Name</th>
                        <th>Code</th>
                        <th>Procedure</th>
                        <th>Admission Date</th>
                        <th>Due Deadline</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dues as $d)
                        @php
                            $computed = $d->status === 'paid' ? 'paid' : ($d->due_deadline < now()->toDateString() ? 'waived' : 'pending');
                        @endphp
                        <tr>
                            <td>{{ $d->id }}</td>
                            <td>{{ $d->patientname ?? '-' }}</td>
                            <td>{{ $d->patientcode }}</td>
                            <td>{{ $d->procedure_name ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($d->admission_date)->format('d-m-Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($d->due_deadline)->format('d-m-Y') }}</td>
                            <td>৳{{ number_format($d->amount, 0) }}</td>
                            <td>
                                @if($computed === 'paid')
                                    <span class="badge badge-paid">Paid</span>
                                @elseif($computed === 'waived')
                                    <span class="badge badge-waived">Waived</span>
                                @else
                                    <span class="badge badge-pending">Pending</span>
                                @endif
                            </td>
                            <td>
                                @if($computed !== 'paid')
                                    <button type="button" class="btn btn-sm btn-success"
                                        data-toggle="modal" data-target="#payModal{{ $d->id }}">
                                        <i class="fas fa-check"></i> Mark Paid
                                    </button>

                                    <div class="modal fade" id="payModal{{ $d->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-success text-white">
                                                    <h5 class="modal-title">Mark Follow-up Due as Paid</h5>
                                                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                                </div>
                                                <form method="POST" action="{{ route('followupdues.markPaid', $d->id) }}">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <p><strong>Patient:</strong> {{ $d->patientname ?? $d->patientcode }}</p>
                                                        <p><strong>Amount Due:</strong> ৳{{ number_format($d->amount, 0) }}</p>
                                                        <div class="form-group">
                                                            <label>Paid Amount <span class="text-danger">*</span></label>
                                                            <input type="number" step="0.01" name="paid_amount"
                                                                class="form-control" value="{{ $d->amount }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="fas fa-save"></i> Save Payment
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small">Paid on {{ $d->resolved_at ? \Carbon\Carbon::parse($d->resolved_at)->format('d-m-Y') : '-' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No follow-up dues found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $dues->withQueryString()->links() }}
        </div>
    </div>
</div>

@endsection

@push('js')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script>
$(function () {
    $('#duesTable').DataTable({
        paging: false,
        info: false,
        ordering: true,
        searching: true,
        language: {
            search: '<i class="fas fa-search"></i> Search:',
            searchPlaceholder: 'Type to filter...'
        }
    });
});
</script>
@endpush
