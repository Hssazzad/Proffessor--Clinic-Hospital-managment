@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Payment List</h4>
    </div>

    {{-- ===================== Filter Form ===================== --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('billing.paylist.v2') }}" class="row gy-2 gx-2 align-items-end">

                <div class="col-md-3">
                    <label class="form-label small mb-1">Search (Name / Code / Mobile / Bill No)</label>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="Search...">
                </div>

                <div class="col-md-3">
                    <label class="form-label small mb-1">Test / Service</label>
                    <select name="service_name" class="form-control">
                        <option value="">-- All Tests --</option>
                        @foreach($services as $s)
                            <option value="{{ $s }}" @selected(($filters['service_name'] ?? '') === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small mb-1">Payment Source</label>
                    <select name="payment_source" class="form-control">
                        <option value="all" @selected(($filters['payment_source'] ?? 'all') === 'all')>All Sources</option>
                        <option value="reception" @selected(($filters['payment_source'] ?? '') === 'reception')>Reception</option>
                        <option value="manager" @selected(($filters['payment_source'] ?? '') === 'manager')>Manager</option>
                        <option value="legacy" @selected(($filters['payment_source'] ?? '') === 'legacy')>Legacy</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small mb-1">Date Mode</label>
                    <select name="date_mode" class="form-control">
                        <option value="all" @selected(($filters['date_mode'] ?? 'all') === 'all')>All Time</option>
                        <option value="single" @selected(($filters['date_mode'] ?? '') === 'single')>Single Date</option>
                        <option value="range" @selected(($filters['date_mode'] ?? '') === 'range')>Date Range</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small mb-1">Date Column</label>
                    <select name="date_column" class="form-control">
                        <option value="PaymentDate" @selected(($filters['date_column'] ?? 'PaymentDate') === 'PaymentDate')>Payment Date</option>
                        <option value="created_at" @selected(($filters['date_column'] ?? '') === 'created_at')>Created At</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small mb-1">Single Date</label>
                    <input type="date" name="single_date" value="{{ $filters['single_date'] ?? now()->toDateString() }}" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label small mb-1">From</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? now()->toDateString() }}" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label small mb-1">To</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? now()->toDateString() }}" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label small mb-1">Per Page</label>
                    <select name="per_page" class="form-control">
                        @foreach([20, 50, 100, 200] as $pp)
                            <option value="{{ $pp }}" @selected((int)($filters['per_page'] ?? 20) === $pp)>{{ $pp }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('billing.paylist.v2') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>

            </form>
        </div>
    </div>

    {{-- ===================== Summary Strip ===================== --}}
    <div class="row mb-3">
        <div class="col-md-2">
            <div class="card text-center"><div class="card-body py-2">
                <div class="small text-muted">Invoices</div>
                <div class="h5 mb-0">{{ $summary->total_invoices ?? 0 }}</div>
            </div></div>
        </div>
        <div class="col-md-2">
            <div class="card text-center"><div class="card-body py-2">
                <div class="small text-muted">Patients</div>
                <div class="h5 mb-0">{{ $summary->total_patients ?? 0 }}</div>
            </div></div>
        </div>
        <div class="col-md-2">
            <div class="card text-center"><div class="card-body py-2">
                <div class="small text-muted">Total Bill</div>
                <div class="h5 mb-0">? {{ number_format($summary->total_bill ?? 0, 2) }}</div>
            </div></div>
        </div>
        <div class="col-md-2">
            <div class="card text-center"><div class="card-body py-2">
                <div class="small text-muted">Paid</div>
                <div class="h5 mb-0 text-success">? {{ number_format($summary->total_paid ?? 0, 2) }}</div>
            </div></div>
        </div>
        <div class="col-md-2">
            <div class="card text-center"><div class="card-body py-2">
                <div class="small text-muted">Due</div>
                <div class="h5 mb-0 text-danger">? {{ number_format($summary->total_due ?? 0, 2) }}</div>
            </div></div>
        </div>
        <div class="col-md-2">
            <div class="card text-center"><div class="card-body py-2">
                <div class="small text-muted">Discount</div>
                <div class="h5 mb-0">? {{ number_format($summary->total_discount ?? 0, 2) }}</div>
            </div></div>
        </div>
    </div>

    {{-- ===================== Grouped Payment Table ===================== --}}
    @forelse($grouped as $groupDate => $rows)
        <div class="card mb-3">
            <div class="card-header py-2">
                <strong>{{ \Carbon\Carbon::parse($groupDate)->format('d M, Y') }}</strong>
                <span class="text-muted small">({{ count($rows) }} payment{{ count($rows) > 1 ? 's' : '' }})</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Bill No</th>
                            <th>Patient</th>
                            <th>Mobile</th>
                            <th class="text-end">Total Bill</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr>
                                <td>
                                    {{ $row->BillNo }}
                                    @if($row->payment_source === 'manager')
                                        <span class="badge bg-primary text-white ms-1" style="font-size:10px;">Manager</span>
                                    @elseif($row->payment_source === 'reception')
                                        <span class="badge bg-info text-dark ms-1" style="font-size:10px;">Reception</span>
                                    @else
                                        <span class="badge bg-secondary text-white ms-1" style="font-size:10px;">Legacy</span>
                                    @endif
                                </td>
                                <td>{{ $row->PatientName }} <span class="text-muted small">({{ $row->PatientCode }})</span></td>
                                <td>{{ $row->MobileNo }}</td>
                                <td class="text-end">? {{ number_format($row->TotalBill, 2) }}</td>
                                <td class="text-end">? {{ number_format($row->Discount, 2) }}</td>
                                <td class="text-end text-success">? {{ number_format($row->PaidAmount, 2) }}</td>
                                <td class="text-end text-danger">? {{ number_format($row->DueAmount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="alert alert-info">No payments found for the selected filters.</div>
    @endforelse

    {{-- ===================== Pagination ===================== --}}
    <div class="d-flex justify-content-center">
        {{ $payments->links() }}
    </div>

</div>
@endsection