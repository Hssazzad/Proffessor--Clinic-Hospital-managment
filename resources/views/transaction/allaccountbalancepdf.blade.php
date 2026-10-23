<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 12mm 10mm 12mm 10mm; } 
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 13px; margin: 0; color: #000; line-height: 1.4; }
        h1 { text-align:center; font-size: 24px; margin: 0 0 5px; color: #000; font-weight: bold; } 
        .subtitle { text-align:center; margin: 0 0 10px; font-size: 14px; color: #222; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #333; padding: 4px 5px; vertical-align: middle; } 
        th { background: #3498db; color: #fff; text-align: center; font-weight: bold; font-size: 14px; }
        th:nth-child(1) { width: 5%; }
        th:nth-child(2) { width: 15%; }
        th:nth-child(3) { width: 44%; text-align: left; }
        th:nth-child(4), th:nth-child(5) { width: 18%; text-align: right; }
        .right { text-align: right; white-space: nowrap; }
        .center { text-align: center; }
        .left { text-align: left; }
        .report-header { width: 100%; border: none; margin-bottom: 10px; }
        .report-header td { border: none; padding: 0; }
        .report-logo { width: 55px; height: 55px; object-fit: contain; }
        .report-footer { position: fixed; bottom: -10mm; left: 0; right: 0; text-align: center; font-size: 11px; border-top: 1px solid #aaa; padding-top: 5px; }
        .page-number:after { content: "Page " counter(page); }
    </style>
</head>
<body>
@php
    $logoPath = public_path('images/PC.png');
    $logoData = is_file($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
@endphp

@unless($onlyData)
<table class="report-header">
    <tr>
        <td style="width:60px; text-align:left;">
            @if($logoData)
                <img class="report-logo" src="{{ $logoData }}">
            @endif
        </td>
        <td class="center">
            <h1>{{ config('app.name') }} - All Account Ledger</h1>
        </td>
        <td style="width:60px;"></td>
    </tr>
</table>
@endunless

<div class="subtitle">
    <strong>Date:</strong> {{ $reportDate }} &nbsp;|&nbsp; <strong>Type:</strong> {{ $typeLabel }}
</div>

<table>
    <thead>
        <tr>
            <th>SL</th>
            <th>Account No</th>
            <th class="left">Account Name</th>
            <th class="right">Positive Balance</th>
            <th class="right">Negative Balance</th>
        </tr>
    </thead>
    <tbody>
        @php 
            $totalPos = 0; 
            $totalNeg = 0; 
        @endphp
        @foreach($accounts as $i => $account)
            @php 
                $balance = (float) $account->balance; 
                if($balance > 0) $totalPos += $balance;
                if($balance < 0) $totalNeg += $balance;
            @endphp
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td class="center">{{ $account->AccountNo }}</td>
                <td class="left">{{ trim($account->title) }}</td>
                <td class="right">{{ $balance > 0 ? number_format($balance, 2) : '' }}</td>
                <td class="right">{{ $balance < 0 ? number_format($balance, 2) : '' }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="3" class="right" style="color:#000; background:#f0f0f0;">Total</th>
            <th class="right" style="color:#000; background:#f0f0f0;">{{ number_format($totalPos, 2) }}</th>
            <th class="right" style="color:#000; background:#f0f0f0;">{{ number_format($totalNeg, 2) }}</th>
        </tr>
    </tfoot>
</table>

<div class="report-footer">
    <span class="page-number"></span> | {{ config('app.name') }}
</div>
</body>
</html>
