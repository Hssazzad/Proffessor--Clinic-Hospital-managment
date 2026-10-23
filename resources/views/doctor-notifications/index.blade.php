@extends('adminlte::page')

@section('title', 'Notifications')

@section('content_header')
<h1>Notifications</h1>
@stop

@section('content')
<div class="card shadow-sm">
    <div class="card-header"><h3 class="card-title">My USG Follow-up Notifications</h3></div>
    <div class="card-body">
        <div class="list-group">
            @forelse($notifications as $n)
                @php
                    $due = $n->followup && $n->followup->followup_date <= now()->toDateString();
                @endphp
                <div class="list-group-item {{ $due ? 'list-group-item-warning' : '' }}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">{{ $n->title }}</h6>
                            <p class="mb-1 text-muted">{{ $n->message }}</p>
                            <small>
                                @if($n->followup)
                                    Follow-up: {{ $n->followup->followup_date->format('d M Y') }}
                                    — <span class="badge badge-{{ $n->followup->status == 'pending' ? 'warning' : ($n->followup->status == 'contacted' ? 'info' : 'success') }}">{{ ucfirst($n->followup->status) }}</span>
                                    @if($n->followup->patient->mobile_no)
                                        | <a href="tel:{{ $n->followup->patient->mobile_no }}">{{ $n->followup->patient->mobile_no }}</a>
                                    @endif
                                @endif
                            </small>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-{{ $due ? 'danger' : 'secondary' }} mb-2">{{ $due ? 'Overdue/Due' : 'Upcoming' }}</span>
                            <br>
                            @if($n->followup)
                                <a href="{{ route('usg-reports.show', $n->followup->usg_report_id) }}" class="btn btn-info btn-xs"><i class="fas fa-eye"></i> View</a>
                            @endif
                            <form action="{{ route('notifications.read', $n->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-default btn-xs">Mark Read</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="list-group-item text-center text-muted">No notifications.</div>
            @endforelse
        </div>

        {{ $notifications->links() }}
    </div>
</div>
@stop
