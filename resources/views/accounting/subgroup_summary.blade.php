@extends('adminlte::page')
@section('title', 'Sub Group Summary')
@section('content_header')<h1>Sub Group Summary</h1>@stop
@section('content')
<div class="card"><div class="card-body"><form method="post" action="{{ route('accounting.showsubgroupsummary') }}">@csrf
<div class="input-group"><select name="subgroup" class="form-control" required><option value="">Select sub group</option>@foreach($subgroups as $group)<option value="{{ $group->gl_sub_group }}" @selected(($selected ?? '') == $group->gl_sub_group)>{{ $group->gl_sub_group }} - {{ $group->gl_sub_group_name }}</option>@endforeach</select><div class="input-group-append"><button class="btn btn-primary">Show</button></div></div></form></div></div>
@if($accounts->isNotEmpty())<div class="card"><div class="card-body p-0"><table class="table table-bordered mb-0"><thead><tr><th>Account No</th><th>Title</th><th class="text-right">Balance</th></tr></thead><tbody>@foreach($accounts as $account)<tr><td>{{ $account->AccountNo }}</td><td>{{ $account->title }}</td><td class="text-right">{{ number_format((float)$account->current_balance, 2) }}</td></tr>@endforeach</tbody></table></div></div>@endif
@stop
