@extends('adminlte::page')

@section('title', 'Multiple Cash Receive')
@section('plugins.Select2', true)

@section('content_header')
    <h1>Multiple Cash Receive</h1>
@stop

@section('content')
@if(!empty($lastReceipt))
<div class="card card-outline card-primary mb-3">
    <div class="card-header">
        <h3 class="card-title font-weight-bold">
            <i class="fas fa-receipt mr-1"></i> Last Saved Payment — Receipt #{{ $lastReceipt['receipt_no'] }}
            <span class="badge badge-warning ml-2">Pending Authorization</span>
        </h3>
    </div>
    <div class="card-body py-2">
        <div class="row text-center">
            <div class="col-md-4"><strong>Items:</strong> {{ $lastReceipt['count'] }}</div>
            <div class="col-md-4"><strong>Total:</strong> <span class="text-primary font-weight-bold">Tk. {{ number_format((float) $lastReceipt['total'], 2) }}</span></div>
            <div class="col-md-4"><strong>Refs:</strong> #{{ implode(', #', $lastReceipt['refs']) }}</div>
        </div>
        <div class="text-center mt-2">
            <a href="{{ route('transaction.authorizereview', ['ref_number' => $lastReceipt['refs'][0]]) }}" class="btn btn-success btn-sm">
                <i class="fas fa-check mr-1"></i> Review / Authorize this receipt
            </a>
            <a href="{{ route('transaction.unauthorizelist') }}" class="btn btn-default btn-sm ml-1">Unauthorized List</a>
        </div>
    </div>
</div>
@endif
<div class="card card-success">
    <div class="card-header"><h3 class="card-title">Last transaction: {{ $refNumber }}</h3></div>
    <form method="POST" action="{{ route('transaction.multiplecashreceiveaction') }}">
        @csrf
        <div class="card-body">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            <div class="form-row mb-3">
                <div class="col-md-4"><label>Transaction Date</label><input id="PostingDate" name="PostingDate" class="form-control" value="{{ old('PostingDate', now()->format('d-m-Y')) }}" readonly></div>
                <div class="col-md-4"><label>Receipt Number</label><input name="Serial" class="form-control" value="{{ old('Serial') }}"></div>
                <div class="col-md-4 pt-4 text-primary">Available Cash: {{ number_format((float) $cashBalance, 2) }}</div>
            </div>
            <div id="receiveRows">
                @foreach(old('rows', [['CreditAccount' => '', 'Amount' => '', 'Particular' => '']]) as $index => $row)
                    <div class="form-row receive-row mb-2">
                        <div class="col-md-5"><select name="rows[{{ $index }}][CreditAccount]" class="form-control select2" required><option value="">Receive From Account</option>@foreach($accounts as $account)<option value="{{ $account->AccountNo }}" @selected(($row['CreditAccount'] ?? '') == $account->AccountNo)>[{{ $account->gl_group }}-{{ $account->gl_sub_group }}] {{ $account->title }}</option>@endforeach</select></div>
                        <div class="col-md-2"><input type="number" min="0.01" step="0.01" name="rows[{{ $index }}][Amount]" class="form-control amount-input" placeholder="Amount" value="{{ $row['Amount'] ?? '' }}" required></div>
                        <div class="col-md-4"><input name="rows[{{ $index }}][Particular]" class="form-control" placeholder="Description" value="{{ $row['Particular'] ?? '' }}"></div>
                        <div class="col-md-1"><button type="button" class="btn btn-outline-danger remove-row">×</button></div>
                    </div>
                @endforeach
            </div>
            @error('rows')<div class="text-danger">{{ $message }}</div>@enderror
            <button id="addRow" type="button" class="btn btn-outline-primary mt-2">+ Add another account</button>
            <div class="form-row mt-3 align-items-center">
                <div class="col-md-5 text-right font-weight-bold">Total Receive:</div>
                <div class="col-md-2"><input id="totalReceive" class="form-control font-weight-bold text-right" readonly value="0.00" tabindex="-1"></div>
            </div>
        </div>
        <div class="card-footer"><button class="btn btn-primary">Save All Transactions</button></div>
    </form>
</div>
<template id="rowTemplate"><div class="form-row receive-row mb-2"><div class="col-md-5"><select name="rows[__INDEX__][CreditAccount]" class="form-control select2" required><option value="">Receive From Account</option>@foreach($accounts as $account)<option value="{{ $account->AccountNo }}">[{{ $account->gl_group }}-{{ $account->gl_sub_group }}] {{ $account->title }}</option>@endforeach</select></div><div class="col-md-2"><input type="number" min="0.01" step="0.01" name="rows[__INDEX__][Amount]" class="form-control amount-input" placeholder="Amount" required></div><div class="col-md-4"><input name="rows[__INDEX__][Particular]" class="form-control" placeholder="Description"></div><div class="col-md-1"><button type="button" class="btn btn-outline-danger remove-row">×</button></div></div></template>
@stop

@push('js')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script>
$(function () {
    $('#PostingDate').datepicker({ dateFormat: 'dd-mm-yy' });
    $('.select2').select2({ width: '100%', dropdownParent: $(document.body) });
    let index = $('#receiveRows .receive-row').length;
    function updateTotal() {
        let total = 0;
        $('#receiveRows .amount-input').each(function () { total += parseFloat($(this).val()) || 0; });
        $('#totalReceive').val(total.toFixed(2));
    }
    $(document).on('input', '#receiveRows .amount-input', updateTotal);
    $('#addRow').on('click', function () {
        const row = $($('#rowTemplate').html().replaceAll('__INDEX__', index++));
        $('#receiveRows').append(row); row.find('.select2').select2({ width: '100%', dropdownParent: $(document.body) });
        updateTotal();
    });
    $(document).on('click', '.remove-row', function () { if ($('#receiveRows .receive-row').length > 1) $(this).closest('.receive-row').remove(); updateTotal(); });
    updateTotal();
});
</script>
@endpush
