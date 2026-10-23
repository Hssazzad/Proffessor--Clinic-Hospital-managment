<div class="data-report" id="account-ledger-data">
    <div class="data-report-heading"><strong>All Account Ledger</strong> — {{ $reportDate }}</div>
    <div class="table-responsive">
    <table class="table table-sm table-bordered mb-0"><thead><tr><th>SL</th><th>Account No</th><th>Account Name</th><th>Positive Balance</th><th>Negative Balance</th></tr></thead><tbody>
    @forelse($accounts as $i => $account)
        @php $balance = (float) $account->balance; @endphp
        <tr><td>{{ $i + 1 }}</td><td><a href="{{ route('transaction.showledger', ['AccountNo' => $account->AccountNo]) }}">{{ $account->AccountNo }}</a></td><td>{{ trim($account->title) }}</td><td class="number">{{ $balance > 0 ? number_format($balance, 2) : '' }}</td><td class="number">{{ $balance < 0 ? number_format($balance, 2) : '' }}</td></tr>
    @empty
        <tr><td colspan="5" class="empty">No account data found.</td></tr>
    @endforelse
    </tbody></table>
    </div>
</div>
