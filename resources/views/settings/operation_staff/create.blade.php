@extends('adminlte::page')

@section('title', 'Add ' . $singular)

@section('content_header')
  <h1 class="text-primary">Add {{ $singular }}</h1>
@stop

@section('content')

@if ($errors->any())
  <div class="alert alert-danger">
    <b>Fix the following errors:</b>
    <ul class="mb-0">
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif

@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
  <div class="card-body">
    <form action="{{ route($store) }}" method="post">
      @csrf

      <div class="form-group col-md-6 p-0">
        <label>Name</label>
        <input type="text" name="name" class="form-control"
               value="{{ old('name') }}" maxlength="255" required>
      </div>

      <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" name="active" id="active" value="1" checked>
        <label class="form-check-label" for="active">Active</label>
      </div>

      <button class="btn btn-primary">Save</button>
      <a href="{{ route($index) }}" class="btn btn-secondary">
        Back to List
      </a>
    </form>
  </div>
</div>

@stop
