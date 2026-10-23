@extends('adminlte::page')
@section('title', 'Net Profit Loss')
@section('content_header')<h1>Net Profit / Loss</h1>@stop
@section('content')<div class="card"><div class="card-body p-0"><table class="table table-bordered mb-0"><thead><tr><th>Category</th><th class="text-right">Balance</th></tr></thead><tbody>@foreach($groups as $group)<tr><td>{{ $group->gl_group_name }}</td><td class="text-right">{{ number_format((float)$group->balance, 2) }}</td></tr>@endforeach</tbody></table></div></div>@stop
