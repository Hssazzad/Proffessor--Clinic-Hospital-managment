@extends('adminlte::page')

@section('title', 'General Ledger')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center no-print">
        <h1 class="m-0 text-dark">General Ledger Overview</h1>
    </div>
@stop

@section('content')

{{-- ============ Report ============ --}}
<div class="card">
    <div class="card-body" id="printArea">

        <div class="text-center mb-3">
            <h3 class="mb-0 font-weight-bold">{{ config('app.name') }}</h3>
            <h5 class="mt-2 mb-0">General Ledger Overview</h5>
        </div>

        <div class="row">

            {{-- Income Statement --}}
            <div class="col-md-6">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Income Statement</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Account Group</th>
                                    <th class="text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($income as $row)
                                    <tr>
                                        <td>{{ $row->gl_group_name }}</td>
                                        <td class="text-right">{{ number_format((float) $row->balance, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">No income records found.</td>
                                    </tr>
                                @endforelse
                                <tr class="font-weight-bold bg-light">
                                    <td>Total Income</td>
                                    <td class="text-right">{{ number_format((float) $totalIncome, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Balance Sheet --}}
            <div class="col-md-6">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Balance Sheet</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Account Group</th>
                                    <th class="text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Assets --}}
                                <tr class="bg-light">
                                    <td colspan="2"><b>Assets</b></td>
                                </tr>
                                @forelse($assets as $row)
                                    <tr>
                                        <td>{{ $row->gl_group_name }}</td>
                                        <td class="text-right">{{ number_format((float) $row->balance, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">No asset records found.</td>
                                    </tr>
                                @endforelse
                                <tr class="font-weight-bold">
                                    <td>Total Asset</td>
                                    <td class="text-right">{{ number_format((float) $totalAsset, 2) }}</td>
                                </tr>

                                {{-- Liabilities --}}
                                <tr class="bg-light">
                                    <td colspan="2"><b>Liabilities</b></td>
                                </tr>
                                @forelse($liabilities as $row)
                                    <tr>
                                        <td>{{ $row->gl_group_name }}</td>
                                        <td class="text-right">{{ number_format((float) $row->balance, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">No liability records found.</td>
                                    </tr>
                                @endforelse
                                <tr class="font-weight-bold">
                                    <td>Total Liability</td>
                                    <td class="text-right">{{ number_format((float) $totalLiability, 2) }}</td>
                                </tr>

                                {{-- Net Income --}}
                                <tr style="border-top: 2px solid #000;">
                                    <td><b>Net Income (Income − Expenditure)</b></td>
                                    <td class="text-right">
                                        <b class="{{ $netIncome >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format((float) $netIncome, 2) }}{{ $netIncome < 0 ? ' (Loss)' : '' }}
                                        </b>
                                    </td>
                                </tr>
                                <tr class="font-weight-bold">
                                    <td>Total Liabilities &amp; Income</td>
                                    <td class="text-right">{{ number_format((float) $totalLiabEquity, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>{{-- /.row --}}

        {{-- Expenditure Statement --}}
        <div class="row">
            <div class="col-md-6">
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Expenditure Statement</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Account Group</th>
                                    <th class="text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenses as $row)
                                    <tr>
                                        <td>{{ $row->gl_group_name }}</td>
                                        <td class="text-right">{{ number_format((float) $row->balance, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">No expenditure records found.</td>
                                    </tr>
                                @endforelse
                                <tr class="font-weight-bold bg-light">
                                    <td>Total Expenditure</td>
                                    <td class="text-right">{{ number_format((float) $totalExpenditure, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>{{-- /.row --}}

    </div>{{-- /#printArea --}}

    <div class="card-footer no-print text-right">
        <button type="button" onclick="window.print()" class="btn btn-sm btn-secondary">
            <i class="fas fa-print"></i> Print
        </button>
    </div>
</div>

@stop

@section('css')
<style>
    #printArea table th, #printArea table td { vertical-align: middle; }

    @media print {
        .no-print,
        .main-header,
        .main-sidebar,
        .main-footer,
        .content-header { display: none !important; }

        .content-wrapper { margin: 0 !important; padding: 0 !important; background: #fff !important; }
        .card { border: 0 !important; box-shadow: none !important; }
        .card-outline { border-top: 0 !important; }
        .bg-light { background: #f2f2f2 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>
@stop
