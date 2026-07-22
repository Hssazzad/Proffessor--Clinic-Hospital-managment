@extends('adminlte::page')

@section('plugins.Select2', true)
@section('title', 'Admit Patient')

@push('css')
<style>
    .select2-container--bootstrap4 .select2-selection {
        border: 1px solid #6b7280 !important;
        border-radius: 6px !important;
        min-height: 38px !important;
        padding: 4px !important;
    }
</style>
@endpush

@section('content_header')
    <h1 class="text-primary font-weight-bold">Admit Patient</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card card-outline card-primary shadow">
    <div class="card-body">

        <form method="POST" action="{{ route('admission.showadMedicine') }}">
            @csrf

            <div class="form-group">
                <label><b>Select Patient <span class="text-danger">*</span></b></label>

                <select name="patientcode" id="patientcode" class="form-control select2" required>
                    <option value="">-- Search by Name or Mobile --</option>

                    @foreach($patients as $p)
                        <option value="{{ $p->patientcode }}">
                            {{ $p->patientname }} - {{ $p->patientcode ?? '' }} ({{ $p->mobile_no ?? 'No Mobile' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <hr>

            <div class="mb-2">
                <button type="submit" class="btn btn-info btn-sm">
                    <i class="fas fa-pills"></i> Load Medicine
                </button>
            </div>

        </form>

    </div>
</div>

@endsection

@push('js')
<script>
$(document).ready(function () {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
});
</script>
@endpush