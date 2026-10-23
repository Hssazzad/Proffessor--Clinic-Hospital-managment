@extends('adminlte::page')

@section('title', 'Investigation Revenue & Patient Report')

@push('css')
<style>
    .report-print-header { display: none; }
    .money { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .report-table th, .report-table td { vertical-align: middle; }
    .report-table tbody tr.total-row td { font-weight: 700; background: #f4f6f9; border-top: 2px solid #dee2e6; }
    .period-cell { white-space: nowrap; }
    @media print {
        @page { size: A4 portrait; margin: 15mm 12mm; }
        .main-header, .main-sidebar, .content-header, .report-filters, .no-print { display: none !important; }
        .content-wrapper { margin-left: 0 !important; padding: 0 !important; }
        .report-print-header { display: block !important; text-align: center; margin-bottom: 12px; }
        body { font-size: 18px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .report-table th, .report-table td { font-size: 18px; padding: 8px 10px; }
        .report-table td a { color: #000 !important; text-decoration: none !important; pointer-events: none !important; }
        .report-print-header h4 { font-size: 28px; margin-bottom: 4px; }
        .report-print-header h5 { font-size: 24px; margin-bottom: 6px; }
        .report-print-header .small { font-size: 16px; }
        .card-header h3 { font-size: 22px; }
        .card { border: none !important; box-shadow: none !important; page-break-inside: avoid; }
        .card-header { background: #fff !important; border-bottom: 1px solid #dee2e6 !important; }
        .table { width: 100% !important; }
        .table-responsive { overflow: visible !important; }
        .badge { border: 1px solid #999 !important; }
        .total-row td { font-weight: 700 !important; border-top: 2px solid #333 !important; }
    }
</style>
@endpush

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="m-0" style="font-size:18px; font-weight:600; color:#222;">
            <i class="fas fa-chart-bar text-primary mr-2"></i>Investigation Revenue & Patient Report
        </h1>
        <ol class="breadcrumb float-sm-right mb-0" style="font-size:12px;">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('billing.invoice.index') }}">Billing</a></li>
            <li class="breadcrumb-item active">Investigation Report</li>
        </ol>
    </div>
@stop

@section('content')

    <div class="report-print-header mb-3">
        <h4 class="mb-0">{{ config('app.name', 'Clinic') }}</h4>
        <h5 class="mb-1">Investigation Revenue & Patient Report</h5>
        <div class="small text-muted">
            Period: {{ $filters['date_from'] }} &rarr; {{ $filters['date_to'] }}
            ({{ $filters['grouping'] === 'monthly' ? 'Monthly' : 'Daily' }} grouping)
            &nbsp;|&nbsp; Generated: {{ now()->format('d M Y h:i A') }}
        </div>
    </div>

    {{-- ===================== Filter Form ===================== --}}
    <div class="card report-filters mb-3">
        <div class="card-header py-2">
            <h3 class="card-title"><i class="fas fa-filter text-primary mr-2"></i>Filters</h3>
        </div>
        <div class="card-body py-3">
            <form method="GET" action="{{ route('billing.investigation-report.index') }}" class="row gy-2 gx-2 align-items-end">

                <div class="col-md-3">
                    <label class="form-label small mb-1">From Date</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label small mb-1">To Date</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label small mb-1">Category</label>
                    <select name="category" class="form-control">
                        <option value="">-- All Categories --</option>
                        @foreach($categories as $c)
                            <option value="{{ $c }}" @selected(($filters['category'] ?? '') === $c)>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small mb-1">Grouping</label>
                    <select name="grouping" class="form-control">
                        <option value="daily" @selected(($filters['grouping'] ?? 'daily') === 'daily')>Daily</option>
                        <option value="monthly" @selected(($filters['grouping'] ?? '') === 'monthly')>Monthly</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter mr-1"></i>Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('billing.investigation-report.index') }}" class="btn btn-outline-secondary w-100"><i class="fas fa-undo mr-1"></i>Reset</a>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-primary w-100" onclick="window.print()"><i class="fas fa-print mr-1"></i>Print</button>
                </div>

            </form>
        </div>
    </div>

    {{-- ===================== Category Summary ===================== --}}
    <div class="card mb-3">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0"><i class="fas fa-th-large text-primary mr-2"></i>Revenue by Category</h3>
            <span class="text-muted small">{{ $filters['date_from'] }} &rarr; {{ $filters['date_to'] }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover report-table mb-0">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th class="text-end">Tests</th>
                        <th class="text-end">Patients</th>
                        <th class="money">Discount</th>
                        <th class="money">Revenue</th>
                        <th class="text-end">% of Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @php $grandRevenue = $categoryTotals->last() ? (float) $categoryTotals->last()->revenue : 0; @endphp
                    @foreach($categoryTotals as $row)
                        @if($row->category === 'Total')
                            <tr class="total-row">
                                <td>Total</td>
                                <td class="text-end">{{ number_format($row->tests) }}</td>
                                <td class="text-end">{{ number_format($row->patients) }}</td>
                                <td class="money">{{ number_format($row->discount, 2) }}</td>
                                <td class="money">{{ number_format($row->revenue, 2) }}</td>
                                <td class="text-end">100%</td>
                            </tr>
                        @else
                            <tr>
                                <td>
                                    <a href="{{ route('billing.investigation-report.index', [
                                        'date_from' => $filters['date_from'],
                                        'date_to'   => $filters['date_to'],
                                        'grouping'  => $filters['grouping'],
                                        'category'  => $row->category,
                                    ]) }}">
                                        {{ $row->category }}
                                    </a>
                                    @if(($filters['category'] ?? null) === $row->category)
                                        <span class="badge badge-primary ml-1">drill-down</span>
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format($row->tests) }}</td>
                                <td class="text-end">{{ number_format($row->patients) }}</td>
                                <td class="money">{{ number_format($row->discount, 2) }}</td>
                                <td class="money">{{ number_format($row->revenue, 2) }}</td>
                                <td class="text-end">
                                    @if($grandRevenue > 0) {{ number_format(($row->revenue / $grandRevenue) * 100, 1) }}% @else 0% @endif
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===================== Service Breakdown ===================== --}}
    <div class="card mb-3">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="fas fa-list-ul text-primary mr-2"></i>
                {{ $filters['category'] ? $filters['category'] . ' — Service Breakdown' : 'Service Breakdown (All Categories)' }}
            </h3>
            <span class="text-muted small">
                @if($filters['category'])
                    <a href="{{ route('billing.investigation-report.index', [
                        'date_from' => $filters['date_from'],
                        'date_to'   => $filters['date_to'],
                        'grouping'  => $filters['grouping'],
                    ]) }}" class="text-danger mr-2"><i class="fas fa-times-circle mr-1"></i>Clear category</a>
                @endif
                {{ $serviceBreakdown->count() }} service{{ $serviceBreakdown->count() === 1 ? '' : 's' }}
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover report-table mb-0">
                <thead>
                    <tr>
                        @if(!($filters['category'] ?? null))
                            <th>Category</th>
                        @endif
                        <th>Service / Test Name</th>
                        <th class="text-end">Tests</th>
                        <th class="money">Discount</th>
                        <th class="money">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($serviceBreakdown as $row)
                        <tr>
                            @if(!($filters['category'] ?? null))
                                <td>{{ $row->category }}</td>
                            @endif
                            <td>{{ $row->ServiceName }}</td>
                            <td class="text-end">{{ number_format($row->tests) }}</td>
                            <td class="money">{{ number_format($row->discount, 2) }}</td>
                            <td class="money">{{ number_format($row->revenue, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($filters['category'] ?? null) ? 4 : 5 }}" class="text-center text-muted py-3">
                                No services found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===================== Patient Count ===================== --}}
    <div class="card mb-3">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0"><i class="fas fa-users text-primary mr-2"></i>Patient Count ({{ $filters['grouping'] === 'monthly' ? 'Monthly' : 'Daily' }})</h3>
            <span class="text-muted small">{{ $filters['date_from'] }} &rarr; {{ $filters['date_to'] }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover report-table mb-0">
                <thead>
                    <tr>
                        <th>{{ $filters['grouping'] === 'monthly' ? 'Month' : 'Date' }}</th>
                        <th class="text-end">Invoices</th>
                        <th class="text-end">Distinct Patients</th>
                        <th class="money">Discount</th>
                        <th class="money">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patientCounts as $row)
                        <tr>
                            <td class="period-cell">
                                {{ $filters['grouping'] === 'monthly'
                                    ? \Carbon\Carbon::parse($row->period)->format('F Y')
                                    : \Carbon\Carbon::parse($row->period)->format('d M Y') }}
                            </td>
                            <td class="text-end">{{ number_format($row->invoices) }}</td>
                            <td class="text-end">{{ number_format($row->patients) }}</td>
                            <td class="money">{{ number_format($row->discount, 2) }}</td>
                            <td class="money">{{ number_format($row->revenue, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">
                                No patient activity found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===================== Patient-wise Test Breakdown ===================== --}}
    <div class="card mb-3">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="fas fa-vials text-primary mr-2"></i>
                {{ $filters['category'] ? $filters['category'] . ' — Patient-wise Test Breakdown' : 'Patient-wise Test Breakdown (All Categories)' }}
            </h3>
            <span class="text-muted small">
                {{ $patientBreakdown->count() }} patient{{ $patientBreakdown->count() === 1 ? '' : 's' }}
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover report-table mb-0">
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Patient Code</th>
                        <th>Tests Done</th>
                        <th class="text-end">Test Count</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patientBreakdown as $row)
                        <tr>
                            <td>{{ $row->PatientName }}</td>
                            <td>{{ $row->PatientCode }}</td>
                            <td>{{ $row->tests }}</td>
                            <td class="text-end">{{ number_format($row->test_count) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">
                                No patients found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@stop