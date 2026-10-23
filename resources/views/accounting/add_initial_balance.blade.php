@extends('adminlte::page')
@section('title', 'Add Initial Balance')
@section('content_header')<h1>Add Initial Balance</h1>@stop
@section('content')
<div class="card"><div class="card-body">
    @include('partials.flash')
    <form method="post" action="{{ route('accounting.initialbalanceaction') }}">
        @csrf
        <div class="form-group"><label>Account</label><select name="CreditAccount" class="form-control" required><option value="">Select account</option>@foreach($accounts as $account)<option value="{{ $account->AccountNo }}">{{ $account->AccountNo }} - {{ $account->title }}</option>@endforeach</select></div>
        <div class="form-group"><label>Amount</label><input name="Amount" type="number" step="0.01" min="0" class="form-control" required></div>
        <button class="btn btn-primary">Save</button>
    </form>
</div></div>
@stop
