@extends('adminlte::page')
@section('title', 'Change Account Number')
@section('content_header')<h1>Change Account Number</h1>@stop
@section('content')
<div class="card"><div class="card-body">
    @include('partials.flash')
    <form method="post" action="{{ route('accounting.changeaccountaction') }}">@csrf
        <div class="form-group"><label>Current account</label><select name="OldAccountNo" class="form-control" required>@foreach($accounts as $account)<option value="{{ $account->AccountNo }}">{{ $account->AccountNo }} - {{ $account->title }}</option>@endforeach</select></div>
        <div class="form-group"><label>New account number</label><input name="NewAccount" class="form-control" required></div>
        <div class="form-group"><label>New group</label><input name="NewGlCode" class="form-control" required></div>
        <div class="form-group"><label>New sub group</label><input name="NewSubGlCode" class="form-control" required></div>
        <button class="btn btn-primary">Change</button>
    </form>
</div></div>
@stop
