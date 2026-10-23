@extends('adminlte::page')

@section('title', 'Close of Business')

@section('content_header')
    <h1>Close of Business</h1>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header"><h3 class="card-title">Closing Balance List</h3></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead><tr><th>#</th><th>Event Date</th><th>Account No</th><th>Account Name</th><th class="text-right">Closing Balance</th><th>Posting Date</th><th>Created At</th></tr></thead>
                    <tbody>
                        @forelse($closings as $closing)
                            <tr>
                                <td>{{ $loop->iteration }}</td><td>{{ $closing->EventDate }}</td><td>{{ $closing->AccountNo }}</td>
                                <td>{{ $closing->account_title ?? 'Account name not found' }}</td>
                                <td class="text-right">{{ number_format((float) $closing->Balance, 2) }}</td><td>{{ $closing->PostingDate }}</td><td>{{ $closing->AutoPost }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No closing balance data found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
