@extends('adminlte::page')
@section('title', 'Balance Sheet')
@section('content')
<style>
    .cl3-bs { width: 60%; border-collapse: collapse; margin: 50px auto; }
    .cl3-bs td, .cl3-bs th { padding: 2px; border: 1px solid #ccc; text-align: left; font-size: 18px; }
    .cl3-bs .center { text-align: center; }
    .cl3-bs .amount { text-align: right; }
    .cl3-bs .link { border: 0; background: transparent; cursor: pointer; padding: 0; }
</style>
<table class="cl3-bs">
    <tr><td class="center" colspan="2">{{ config('app.name') }} Balance Sheet</td></tr>
    <tr><td class="center">As on date: {{ $asOnDate }}</td><td class="center">Print date: {{ $printDate }}</td></tr>
</table>
<table class="cl3-bs">
    <tr><td class="center">Liability</td><td class="center">Asset</td></tr>
    <tr>
        <td style="vertical-align:top"><table width="100%" style="border-collapse:collapse">
            @foreach($liabilities as $row)<tr><td><form method="post" action="{{ route('accounting.showgroupdetails') }}">@csrf<input type="hidden" name="gl_group" value="{{ $row->gl_group }}"><button class="link">{{ $row->gl_group_name }}</button></form></td><td class="amount">{{ number_format((float)$row->balance, 2) }}</td></tr>@endforeach
            <tr><td>Total Income</td><td class="amount">{{ number_format((float)$incomeTotal, 2) }}</td></tr>
        </table></td>
        <td style="vertical-align:top"><table width="100%" style="border-collapse:collapse">
            @foreach($assets as $row)<tr><td><form method="post" action="{{ route('accounting.showgroupdetails') }}">@csrf<input type="hidden" name="gl_group" value="{{ $row->gl_group }}"><button class="link">{{ $row->gl_group_name }}</button></form></td><td class="amount">{{ number_format((float)$row->balance, 2) }}</td></tr>@endforeach
            <tr><td>Total Expenditure</td><td class="amount">{{ number_format((float)$expenseTotal, 2) }}</td></tr>
        </table></td>
    </tr>
    <tr class="center">
        <td colspan="2" style="font-weight:bold; color: {{ $netIncome >= 0 ? 'green' : 'red' }};">
            Net Income (after discount/expense): {{ number_format((float) $netIncome, 2) }}{{ $netIncome < 0 ? ' (Loss)' : '' }}
        </td>
    </tr>
    <tr class="center"><td>Total Liability : {{ number_format((float)$totalLiability, 2) }}</td><td>Total Asset: {{ number_format((float)$totalAsset, 2) }}</td></tr>
</table>
@stop
