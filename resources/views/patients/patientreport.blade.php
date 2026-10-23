@extends('adminlte::page')

@section('title', 'Patient Audit Report')

@section('content_header')
    <h1>Patient Audit Report</h1>
@stop

@section('content')
<div class="container-fluid">

    {{-- Filter section (screen only) --}}
    <div class="card card-outline card-success no-print">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('patients.patientreport') }}" id="auditFilterForm">
                <div class="row align-items-end">
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="mb-1 small">From Date</label>
                        <input type="date" name="from_date" id="fromDate" class="form-control form-control-sm" value="{{ $from }}" max="9999-12-31">
                    </div>
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="mb-1 small">To Date</label>
                        <input type="date" name="to_date" id="toDate" class="form-control form-control-sm" value="{{ $to }}" max="9999-12-31">
                    </div>
                    <div class="col-md-3 col-sm-12 mb-2">
                        <label class="mb-1 small">Search Patient (ID / Name / Phone)</label>
                        <input type="text" name="q" class="form-control form-control-sm" placeholder="Patient ID / Name / Phone" value="{{ $q }}">
                    </div>
                    <div class="col-md-5 col-sm-12 mb-2">
                        <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-search mr-1"></i>Search</button>
                        <a href="{{ route('patients.patientreport') }}" class="btn btn-default btn-sm">Reset</a>
                        <button type="button" class="btn btn-dark btn-sm" onclick="window.print()"><i class="fas fa-print mr-1"></i>Print Report</button>
                        <a href="{{ route('patients.patientreport.export', request()->query()) }}" class="btn btn-info btn-sm"><i class="fas fa-file-csv mr-1"></i>Export CSV</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="button" class="btn btn-xs btn-outline-secondary quick-date" data-range="today">Today</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary quick-date" data-range="yesterday">Yesterday</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary quick-date" data-range="week">This Week</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary quick-date" data-range="month">This Month</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary quick-date" data-range="last30">Last 30 Days</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary --}}
    <div class="card card-outline card-info">
        <div class="card-body py-2">
            <div class="row text-center">
                <div class="col-md-6 col-12 mb-1">
                    <span class="text-muted small">Report Period</span><br>
                    <strong>{{ \Carbon\Carbon::parse($from)->format('d M Y') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</strong>
                </div>
                <div class="col-md-6 col-12 mb-1">
                    <span class="text-muted small">Total Patients</span><br>
                    <strong style="font-size:18px;">{{ number_format($total) }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Print header (print only) --}}
    <div class="d-none d-print-block text-center mb-3">
        <h3 class="mb-0">PROFESSOR CLINIC</h3>
        <div>Patient Audit Report</div>
        <div class="small">Report Period: {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</div>
        <div class="small">Total Patients: {{ number_format($total) }}</div>
    </div>

    {{-- Result table --}}
    <div class="card">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped table-bordered text-nowrap mb-0" style="font-size:13px;">
                <thead class="thead-dark text-center">
                    <tr>
                        <th width="50">SL</th>
                        <th>Patient ID</th>
                        <th class="text-left">Patient Name</th>
                        <th width="60">Age</th>
                        <th width="80">Gender</th>
                        <th>Phone</th>
                        <th width="110">Visit Date</th>
                        <th width="100">Visit Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $index => $p)
                        <tr>
                            <td class="text-center">{{ $patients->firstItem() + $index }}</td>
                            <td class="text-center">
                                <a href="{{ route('patients.editpatient', ['id' => $p->id]) }}">{{ $p->patientcode }}</a>
                            </td>
                            <td>{{ $p->patientname }}</td>
                            <td class="text-center">{{ $p->age ?? '-' }}</td>
                            <td class="text-center">{{ $p->gender ?? '-' }}</td>
                            <td class="text-center">{{ $p->mobile_no ?? '-' }}</td>
                            <td class="text-center">{{ $p->registration_date ? \Carbon\Carbon::parse($p->registration_date)->format('d/m/Y') : '-' }}</td>
                            <td class="text-center">{{ $p->registration_date ? \Carbon\Carbon::parse($p->registration_date)->format('h:i A') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No patient records found for the selected period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($patients->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center py-2 no-print">
                <div class="text-muted small">Showing {{ $patients->firstItem() }} to {{ $patients->lastItem() }} of {{ $patients->total() }} patients</div>
                <div>{{ $patients->links('pagination::bootstrap-4') }}</div>
            </div>
        @elseif($patients->total() > 0)
            <div class="card-footer py-2 text-muted small no-print">Showing all {{ $patients->total() }} patients</div>
        @endif
    </div>

</div>
@stop

@section('css')
<style>
    @media print {
        .main-sidebar, .main-header, .main-footer, .no-print { display: none !important; }
        .content-wrapper { margin-left: 0 !important; }
    }
</style>
@stop

@section('js')
<script>
    $(function () {
        function fmt(d) {
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }
        $('.quick-date').on('click', function () {
            var range = $(this).data('range'), now = new Date(), from = new Date(now), to = new Date(now);
            if (range === 'yesterday') { from.setDate(now.getDate() - 1); to.setDate(now.getDate() - 1); }
            else if (range === 'week') { from.setDate(now.getDate() - ((now.getDay() + 6) % 7)); }
            else if (range === 'month') { from.setDate(1); }
            else if (range === 'last30') { from.setDate(now.getDate() - 29); }
            $('#fromDate').val(fmt(from));
            $('#toDate').val(fmt(to));
            $('#auditFilterForm').submit();
        });
    });
</script>
@stop
