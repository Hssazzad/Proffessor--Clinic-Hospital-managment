@extends('adminlte::page')
@section('title', 'Group Details')
@section('content_header')<h1>Group Details</h1>@stop
@section('content')<div class="card"><div class="card-body p-0"><table class="table table-bordered mb-0"><thead><tr><th>Account No</th><th>Title</th><th>Sub Group</th><th class="text-right">Balance</th></tr></thead><tbody>@forelse($accounts as $account)<tr><td>{{ $account->AccountNo }}</td><td>{{ $account->title }}</td><td>{{ $account->gl_sub_group }}</td><td class="text-right">{{ number_format((float)$account->current_balance, 2) }}</td></tr>@empty<tr><td colspan="4" class="text-center">No accounts found.</td></tr>@endforelse</tbody></table></div></div>@stop
