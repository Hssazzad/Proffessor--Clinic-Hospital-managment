@extends('adminlte::page')

@section('title', 'Add Medicine')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h4 class="mb-0 pm-page-title"><b>Medicine Master</b></h4>
        <small class="text-muted">Add and manage common medicines</small>
    </div>
@stop

@section('content')
<style>
    /* ---- Government green form styling ---- */
    .pm-compact .form-control { height: 40px; font-size: 14px; border: 1px solid #ced4da; border-radius: 3px; }
    .pm-compact .form-control:focus { border-color: #198754; box-shadow: 0 0 0 0.1rem rgba(25, 135, 84, 0.15); }
    .pm-compact label { font-size: 14px; font-weight: 600; color: #212529; margin-bottom: 5px; }
    .pm-compact .form-check-label { font-size: 13px; font-weight: 400; color: #6c757d; }
    .pm-compact .form-group { margin-bottom: 12px; }
    .pm-compact .form-text { font-size: 12px; color: #6c757d; margin-top: 4px; }
    .pm-compact .select2-container--bootstrap4 .select2-selection--single { height: 40px; border: 1px solid #ced4da; border-radius: 3px; }
    .pm-compact .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered { line-height: 40px; }
    .pm-compact .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow { height: 38px; }
    .pm-compact .select2-container--bootstrap4.select2-container--focus .select2-selection--single,
    .pm-compact .select2-container--bootstrap4.select2-container--open .select2-selection--single { border-color: #198754; box-shadow: 0 0 0 0.1rem rgba(25, 135, 84, 0.15); }
    .btn-open-type-modal {
        width: 48px; height: 40px; display: inline-flex; align-items: center; justify-content: center;
        border: 1px solid #198754 !important; background: #fff; color: #198754 !important;
        border-radius: 0 3px 3px 0; font-size: 13px; font-weight: 600; padding: 0;
        border-left: 0 !important;
    }
    .btn-open-type-modal:hover { background: #198754 !important; color: #fff !important; }
    .pm-card-body { padding: 16px; }
    .pm-form-row { margin-left: -6px; margin-right: -6px; }
    .pm-form-row > [class*='col-'] { padding-left: 6px; padding-right: 6px; }
    .pm-card-title { font-size: 18px; font-weight: 600; color: #212529; }
    .pm-gov-card { border-top: 3px solid #198754; border-radius: 3px; box-shadow: none; }
    .pm-sticky-bar { min-height: 40px; padding: 8px 12px; background: #eaf5ee; border: 1px solid #bfe3cd; border-radius: 3px; }
    .pm-commonly-used { padding-top: 8px; }
    .pm-card-footer { padding: 10px 16px; }
    .pm-btn-footer { height: 38px; display: inline-flex; align-items: center; justify-content: center; padding: 0 16px; border-radius: 3px; }
    .pm-save-btn { background: #198754; border: 1px solid #198754; color: #fff; }
    .pm-save-btn:hover, .pm-save-btn:focus { background: #157347; border-color: #146c43; color: #fff; }
    .pm-qabtn { background: #198754; border: 1px solid #198754; color: #fff; border-radius: 3px; height: 36px; padding: 0 14px; font-size: 14px; font-weight: 500; }
    .pm-qabtn:hover, .pm-qabtn:focus { background: #157347; border-color: #146c43; color: #fff; }
    .pm-modal-header { background: #198754 !important; border-color: #198754 !important; color: #fff; }
    .pm-modal-header .modal-title { color: #fff; }
    .pm-page-title { color: #198754; }
    .pm-medicine-table th { white-space: nowrap; }
    .pm-medicine-table td { vertical-align: middle; }
</style>

<div class="container-fluid">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <h6 class="font-weight-bold mb-1">Please fix the following errors:</h6>
            <ul class="mb-0 pl-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @include('settings.medicines.partials.create-form')

    {{-- CARD 2 — MEDICINE LIST (AJAX-refreshable region) --}}
    <div id="medicineListRegion">
        @include('settings.medicines.partials.list-table')
    </div>
</div>

@include('settings.medicines.partials.modal-quick-add')
@include('settings.medicines.partials.modal-master-add')
@include('settings.medicines.partials.modal-edit')
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css" rel="stylesheet">
    <style>
        .select2-container--bootstrap4 .select2-selection--single { height: calc(2.25rem + 2px) !important; }
        .input-group > .select2-container--bootstrap4 { flex: 1 1 auto; width: 1% !important; }
        .input-group > .select2-container--bootstrap4 .select2-selection--single {
            border-top-right-radius: 0; border-bottom-right-radius: 0;
        }
        .card-title { font-size: 1.05rem; }
        .form-switch .form-check-input { width: 2.5em; height: 1.25em; cursor: pointer; }
        .form-switch .form-check-input:checked { background-color: #28a745; border-color: #28a745; }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
    @include('settings.medicines.partials.scripts')
@stop
