@extends('adminlte::page')

@section('title', 'USG Follow-ups')

@section('content_header')
<h1>USG Follow-up Tracking</h1>
@stop

@section('content')

<div class="card card-danger">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Overdue Follow-ups</h3></div>
    <div class="card-body">
        @include('usg.partials.followup-rows', ['items' => $overdueFollowups])
    </div>
</div>

<div class="card card-warning">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-day"></i> Today's Follow-ups</h3></div>
    <div class="card-body">
        @include('usg.partials.followup-rows', ['items' => $todayFollowups])
    </div>
</div>

<div class="card card-info">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-clock"></i> Upcoming Follow-ups</h3></div>
    <div class="card-body">
        @include('usg.partials.followup-rows', ['items' => $upcomingFollowups])
    </div>
</div>

<div class="card card-secondary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-phone"></i> Contacted</h3></div>
    <div class="card-body">
        @include('usg.partials.followup-rows', ['items' => $contactedFollowups])
    </div>
</div>

<div class="card card-success">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-check"></i> Completed</h3></div>
    <div class="card-body">
        @include('usg.partials.followup-rows', ['items' => $completedFollowups])
    </div>
</div>

@stop
