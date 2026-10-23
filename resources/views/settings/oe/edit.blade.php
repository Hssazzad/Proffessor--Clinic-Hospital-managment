@extends('adminlte::page')

@section('title', 'Edit O/E Master')

@section('content_header')
    <h1 class="text-primary font-weight-bold">Edit O/E Master</h1>
@stop

@section('content')
    <div class="container-fluid">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-1"></i> Please fix the errors below.
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        @include('settings.oe.form')
    </div>
@stop
