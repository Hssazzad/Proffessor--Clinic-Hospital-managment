<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 24px 28px 32px; }
        body { font-family: DejaVu Sans, sans-serif; color: #000; font-size: 10px; }
        .header { margin-bottom: 16px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { border: 0; vertical-align: middle; }
        .header-logo { width: 22%; text-align: right; padding-right: 12px; }
        .header-logo img { width: 72px; height: auto; }
        .header-name { width: 78%; text-align: left; }
        .header h1 { font-size: 16px; margin: 0 0 8px; text-decoration: underline; }
        .header .date { font-size: 12px; font-weight: bold; text-decoration: underline; }
        .account { width: 100%; margin: 14px 0; font-size: 10px; }
        .statement { width: 100%; border-collapse: collapse; font-size: 9px; }
        .statement th, .statement td { border: 1px solid #000; padding: 5px 4px; }
        .statement th { background: #3498db; color: #fff; text-align: center; }
        .center { text-align: center; }
        .right { text-align: right; }
        .footer { position: fixed; bottom: -16px; left: 0; right: 0; text-align: center; font-size: 9px; }
        .footer:after { content: "Page " counter(page); }
    </style>
</head>
<body>
    <div class="header">
        @php
            $logoPath = public_path('images/PC.png');
            $logoData = is_file($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
        @endphp
        <table class="header-table"><tr>
            <td class="header-logo">@if($logoData)<img src="{{ $logoData }}" alt="{{ config('app.name') }}">@endif</td>
            <td class="header-name"><h1>{{ config('app.name') }} - Account Statement</h1></td>
        </tr></table>
        <div class="date">Date From: {{ $FromDate ?: '-' }} To: {{ $ToDate ?: '-' }}</div>
    </div>

    <table class="account">
        <tr><td><b>Account Title:</b> {{ $AccountTitle }}</td></tr>
        <tr><td><b>Account No:</b> {{ $AccountNo }}</td></tr>
    </table>

    <table class="statement">
        <thead><tr><th>SL No</th><th>Transaction Date</th><th>Reference No</th><th>Particular</th><th>Debit</th><th>Credit</th><th>Balance</th></tr></thead>
        <tbody>
        @php
            $totalDebit = 0;
            $totalCredit = 0;
        @endphp
        @if($rsResult->count())
        @foreach ($rsResult as $i => $row)
            @php
                $debit = (float) ($row->DrAmount ?? 0);
                $credit = (float) ($row->CrAmount ?? 0);
                $totalDebit += $debit;
                $totalCredit += $credit;
                $contra = DB::table('acct_tbl_transaction')->where('RefNumber', $row->RefNumber)->where('AccountNo', '!=', $row->AccountNo)->value('AccountNo');
                $contraTitle = $contra ? DB::table('acct_tbl_balsheet_account')->where('AccountNo', $contra)->value('title') : '';
            @endphp
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td class="center">{{ $row->PostingDate }}</td>
                <td class="center">{{ $row->RefNumber }}</td>
                <td>{{ $row->Particular }} [{{ $contraTitle }}]</td>
                <td class="right">{{ $debit ? number_format($debit, 2) : '' }}</td>
                <td class="right">{{ $credit ? number_format($credit, 2) : '' }}</td>
                <td class="right">{{ number_format((float) ($row->Balance ?? 0), 2) }}</td>
            </tr>
        @endforeach
        @else
            <tr><td colspan="7" class="center">No Transactions Found</td></tr>
        @endif
        </tbody>
        <tfoot><tr><th colspan="4" class="right">Total</th><th class="right">{{ number_format($totalDebit, 2) }}</th><th class="right">{{ number_format($totalCredit, 2) }}</th><th></th></tr></tfoot>
    </table>
    <div class="footer"></div>
</body>
</html>
