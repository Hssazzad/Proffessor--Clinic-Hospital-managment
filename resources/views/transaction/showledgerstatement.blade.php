{{-- Opening Balance Display --}}
@if(isset($OpeningBal))
    <div class="mb-3">
        <strong>Opening Balance:</strong>
        <span class="badge badge-info" style="font-size: 14px; padding: 5px 10px;">
            {{ number_format($OpeningBal, 2) }}
        </span>
    </div>
@endif

{{-- Check if rsResult has data --}}
@if(isset($rsResult) && $rsResult->count() > 0)
    <table id="example" class="display" border="1" align="left" width="100%">
        <thead>
            <tr>
                <th>SL No</th>
                <th>Transaction Date</th>
                <th>Account No</th>
                <th>Name</th>
                <th>Particular</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            @php
                $i = 1;
                $totDebit = 0;
                $totCredit = 0;
            @endphp

            @foreach($rsResult as $row)
                @php
                    $debit = $row->DrAmount ?? 0;
                    $credit = $row->CrAmount ?? 0;

                    if ($debit != 0) {
                        $totDebit = $totDebit + $debit;
                    }
                    if ($credit != 0) {
                        $totCredit = $totCredit + $credit;
                    }

                    // Contra Account Lookup (same as CI3 logic)
                    $contraAccountNo = \DB::table('acct_tbl_transaction')
                        ->where('RefNumber', $row->RefNumber)
                        ->where('AccountNo', '!=', $row->AccountNo)
                        ->value('AccountNo');

                    $contraAccountTitle = $contraAccountNo
                        ? \DB::table('acct_tbl_balsheet_account')
                            ->where('AccountNo', $contraAccountNo)
                            ->value('title')
                        : 'N/A';
                @endphp

                <tr>
                    <td align="center">{{ $i }}</td>
                    <td>{{ $row->PostingDate ?? '-' }}</td>
                    <td align="center">{{ $row->AccountNo ?? '-' }}</td>
                    <td align="center">{{ $AccountTitle ?? 'N/A' }}</td>
                    <td align="center">
                        {{ $row->Particular ?? '-' }}
                        [
                        {{ $contraAccountTitle ?? '-' }}
                        ]
                    </td>
                    <td align="right">
                        @if($debit != 0)
                            {{ number_format($debit, 2) }}
                        @endif
                    </td>
                    <td align="right">
                        @if($credit != 0)
                            {{ number_format($credit, 2) }}
                        @endif
                    </td>
                    <td align="center">{{ number_format($row->Balance ?? 0, 2) }}</td>
                </tr>

                @php $i++; @endphp
            @endforeach
        </tbody>
    </table>

    {{-- Total Row --}}
    <div class="mt-3">
        <strong>Total Debit:</strong> {{ number_format($totDebit, 2) }}
        &nbsp;&nbsp;&nbsp;
        <strong>Total Credit:</strong> {{ number_format($totCredit, 2) }}
    </div>

@else
    {{-- No Data Message --}}
    <div class="alert alert-info" role="alert">
        <h5 class="alert-heading">
            <i class="fas fa-info-circle"></i> No Transactions Found
        </h5>
        <p class="mb-0">
            No ledger entries match your search criteria.
            @if(!empty($AccountNo))
                Please check the account <strong>{{ $AccountNo }}</strong> and date range.
            @else
                Please select an account and try again.
            @endif
        </p>
    </div>
@endif

{{-- Back Button --}}
<div class="mt-3">
    <a href="{{ route('transaction.showledger') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<script>
    $(document).ready(function () {
        if (window.jQuery && $.fn.DataTable && !$.fn.DataTable.isDataTable('#example')) {
            $('#example').DataTable({
                "lengthMenu": [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
                'iDisplayLength': -1,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'print', 'pdf'
                ],
                fixedHeader: {
                    header: true,
                    footer: true
                }
            });
        }
    });
</script>