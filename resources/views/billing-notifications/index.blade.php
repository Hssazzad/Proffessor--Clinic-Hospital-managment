@extends('adminlte::page')

@section('title', 'Billing Notifications')

@section('content_header')
<h1>Billing Notifications</h1>
@stop

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h3 class="card-title">Billing Notification Feed</h3>
        <div class="card-tools">
            <a href="{{ route('billing.notifications.index', ['all' => 1]) }}"
               class="btn btn-default btn-sm">Show All</a>
            <a href="{{ route('billing.notifications.index') }}"
               class="btn btn-default btn-sm">Unread Only</a>
        </div>
    </div>
    <div class="card-body">
        <div class="list-group">
            @forelse($notifications as $n)
                @php
                    $icon = match($n->type) {
                        'invoice_due'        => 'fas fa-file-invoice-dollar',
                        'payment_reference'  => 'fas fa-user-tag',
                        'pending_approval'   => 'fas fa-clock',
                        'overdue_reminder'   => 'fas fa-exclamation-triangle',
                        default              => 'fas fa-bell',
                    };
                    $badge = match($n->type) {
                        'invoice_due'        => 'badge-danger',
                        'payment_reference'  => 'badge-info',
                        'pending_approval'   => 'badge-warning',
                        'overdue_reminder'   => 'badge-danger',
                        default              => 'badge-secondary',
                    };
                @endphp
                <div class="list-group-item {{ $n->is_read ? '' : 'list-group-item-light font-weight-bold' }}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">
                                <i class="{{ $icon }} text-secondary"></i> {{ $n->title }}
                                <span class="badge {{ $badge }}">{{ ucfirst(str_replace('_', ' ', $n->type)) }}</span>
                                @if($n->is_read)
                                    <span class="badge badge-secondary">Read</span>
                                @endif
                            </h6>
                            <p class="mb-1 text-muted">{{ $n->message }}</p>
                            <small>
                                @if($n->bill_no) Bill: <strong>{{ $n->bill_no }}</strong> @endif
                                @if($n->patient_name) | Patient: {{ $n->patient_name }} @endif
                                @if($n->amount !== null) | Amount: ৳ {{ number_format($n->amount, 2) }} @endif
                                @if($n->reference_name) | Ref: {{ $n->reference_name }} @endif
                                @if($n->notified_at) | {{ $n->notified_at->format('d M Y h:i A') }} @endif
                            </small>
                        </div>
                        <div class="text-right">
                            @if(!$n->is_read)
                                <form action="{{ route('billing.notifications.read', $n->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-default btn-xs">Mark Read</button>
                                </form>
                            @endif
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
