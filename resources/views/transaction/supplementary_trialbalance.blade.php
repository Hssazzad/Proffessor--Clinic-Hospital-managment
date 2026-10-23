@extends('adminlte::page')

@section('title', 'Trial Balance')

@section('content_header')
    <h1><i class="fas fa-balance-scale mr-2"></i>Trial Balance</h1>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-body">
            <form method="GET" class="form-inline mb-3">
                <label class="mr-2">As of Date</label>
                <input type="date" name="report_date" class="form-control mr-2" value="{{ $reportDate }}">
                <button class="btn btn-primary"><i class="fas fa-search mr-1"></i> Show Report</button>
                <a href="{{ route('supplementary.trialbalance.pdf', ['report_date' => $reportDate]) }}" class="btn btn-outline-danger ml-2"><i class="fas fa-file-pdf mr-1"></i> Export PDF</a>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-dark"><tr><th>Account No.</th><th>Account Name</th><th class="text-right">Debit</th><th class="text-right">Credit</th></tr></thead>
                    <tbody>
                        @forelse($accounts as $account)
                            <tr>
                                <td>{{ $account->AccountNo }}</td>
                                <td>{{ $account->title }}</td>
                                <td class="text-right">{{ number_format((float) $account->debit, 2) }}</td>
                                <td class="text-right">{{ number_format((float) $account->credit, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No posted transaction found.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="font-weight-bold">
                        <tr><td colspan="2" class="text-right">Total</td><td class="text-right">{{ number_format($totalDebit, 2) }}</td><td class="text-right">{{ number_format($totalCredit, 2) }}</td></tr>
                    </tfoot>
                </table>
            </div>
            <div class="alert alert-{{ abs($totalDebit - $totalCredit) < 0.01 ? 'success' : 'danger' }} mt-3 mb-0">
                {{ abs($totalDebit - $totalCredit) < 0.01 ? 'Debit and Credit are balanced.' : 'Debit and Credit are not balanced.' }}
            </div>
        </div>
    </div>
@stop
