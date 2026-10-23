@extends('adminlte::page')

@section('title', 'Expenditure Statement')

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
        @forelse($expenditure as $row)
            <tr>
                <td>
                    <form method="post" action="{{ route('accounting.showgroupdetails') }}">
                        @csrf
                        <input type="hidden" name="gl_group" value="{{ $row->gl_group }}">
                        <button class="link">{{ $row->gl_group_name }}</button>
                    </form>
                </td>
                <td class="amount">{{ number_format((float) $row->balance, 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="2" class="center">No expenditure groups found.</td></tr>
        @endforelse
        <tr><td><b>Total Expenditure</b></td><td class="amount"><b>{{ number_format((float) $totalExpenditure, 2) }}</b></td></tr>
        </table>
    </td></tr>
</table>
@stop
