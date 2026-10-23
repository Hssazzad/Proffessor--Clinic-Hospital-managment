@extends('adminlte::page')

@section('title', 'PL Statement')

@section('content')
<style>
    .cl3-pl { width: 60%; border-collapse: collapse; margin: 50px auto; }
    .cl3-pl th { background: #3498db; color: white; font-weight: bold; }
    .cl3-pl td, .cl3-pl th { padding: 2px; border: 1px solid #ccc; text-align: left; font-size: 18px; }
    .cl3-pl .center { text-align: center; }
    .cl3-pl .amount { text-align: right; }
    .cl3-pl .link { border: 0; background: transparent; color: green; font-weight: bold; cursor: pointer; padding: 0; }
</style>

<table class="cl3-pl">
    <tr><td class="center" colspan="2">{{ config('app.name') }} Expenditure Statement</td></tr>
    <tr><td class="center">As on date: {{ $asOnDate }}</td><td class="center">Print date: {{ $printDate }}</td></tr>
</table>

<table class="cl3-pl">
    <tr><td class="center">Expenditure</td></tr>
    <tr><td>
        <table width="100%" style="border-collapse:collapse">
        @foreach($expenditure as $row)
            <tr>
                <td><form method="post" action="{{ route('accounting.showgroupdetails') }}">@csrf<input type="hidden" name="gl_group" value="{{ $row->gl_group }}"><button class="link">{{ $row->gl_group_name }}</button></form></td>
                <td class="amount">{{ number_format((float) $row->balance, 2) }}</td>
            </tr>
        @endforeach
        <tr><td>Total</td><td class="amount">{{ number_format((float) $totalExpenditure, 2) }}</td></tr>
        </table>
    </td></tr>
</table>

<table class="cl3-pl">
    <tr><td class="center" colspan="2">{{ config('app.name') }} Income Statement</td></tr>
    <tr><td class="center">As on date: {{ $asOnDate }}</td><td class="center">Print date: {{ $printDate }}</td></tr>
</table>

<table class="cl3-pl">
    <tr><td class="center">Income</td></tr>
    <tr><td>
        <table width="100%" style="border-collapse:collapse">
        @foreach($income as $row)
            <tr><td><form method="post" action="{{ route('accounting.showgroupdetails') }}">@csrf<input type="hidden" name="gl_group" value="{{ $row->gl_group }}"><button class="link">{{ $row->gl_group_name }}</button></form></td><td class="amount">{{ number_format((float) $row->balance, 2) }}</td></tr>
        @endforeach
        <tr><td><b>Total Income</b></td><td class="amount"><b>{{ number_format((float) $totalIncome, 2) }}</b></td></tr>
        <tr>
            <td><b>Net Income (after discount/expense)</b></td>
            <td class="amount" style="color: {{ $netIncome >= 0 ? 'green' : 'red' }}; font-weight: bold;">
                {{ number_format((float) $netIncome, 2) }}{{ $netIncome < 0 ? ' (Loss)' : '' }}
            </td>
        </tr>
        </table>
    </td></tr>
</table>
@stop
