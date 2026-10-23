@extends('adminlte::page')

@section('title', 'Patient Medicine')

@section('content_header')
    <h1 class="text-primary font-weight-bold">
         Round Medicine- Patient Code: {{ $patientcode }}
    </h1>

    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm mt-2">
        Back
    </a>
@stop

@section('content')

<div id="ajaxMessage"></div>

<input type="hidden" id="patientcode" value="{{ $patientcode }}">

<div class="card card-outline card-primary shadow">
    <div class="card-header">
        <button type="button" class="btn btn-success btn-sm" id="addMedicineBtn">
            Add Medicine Fresh
        </button>
    </div>

   <div class="card-body" id="medicineTableArea">
        @include('admission.roundpatient.roundmedicine_rows')
    </div>


<div class="modal fade" id="medicineModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="medicineForm">
            @csrf

            <input type="hidden" name="id" id="medicine_id">
            <input type="hidden" name="patientcode" id="modal_patientcode" value="{{ $patientcode }}">

            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Add Medicine</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Medicine Name</label>
                           
                             <div class="form-group">
                                <label><b>Select Medicine <span class="text-danger">*</span></b></label>

                                <select name="name" id="name" class="form-control select2" required>
                                    <option value="">-- Search by Name </option>

                                    @foreach($medicine as $m)
                                        <option value="{{ $m->name }}">
                                            {{ $m->name }} - {{ $m->name ?? '' }} ({{ $m->name ?? 'No Name' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Strength</label>
                            <input type="text" name="strength" id="strength" class="form-control">
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Morning</label>
                            <input type="text" name="morning" id="morning" class="form-control">
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Noon</label>
                            <input type="text" name="noon" id="noon" class="form-control">
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Night</label>
                            <input type="text" name="night" id="night" class="form-control">
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Order Type</label>
                            <input type="text" name="order_type" id="order_type" class="form-control" value="fresh">
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Template ID</label>
                            <input type="text" name="templeteid" id="templeteid" class="form-control" value="TPL-000001">
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-sm">
                        Save
                    </button>

                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

@endsection

@push('js')
<script>
$(document).ready(function () {

    $('#addMedicineBtn').on('click', function () {
        $('#medicineForm')[0].reset();
        $('#medicine_id').val('');
        $('#modal_patientcode').val($('#patientcode').val());
        $('#templeteid').val('TPL-000001');
        $('#order_type').val('fresh');
        $('#modalTitle').text('Add Medicine');
        $('#medicineModal').modal('show');
    });

    $('#medicineForm').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: "{{ route('admission.freshmedicineSave.save') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function (response) {
                $('#medicineModal').modal('hide');
                $('#ajaxMessage').html('<div class="alert alert-success">' + response.message + '</div>');
                loadMedicineTable();
            },
            error: function () {
                $('#ajaxMessage').html('<div class="alert alert-danger">Something went wrong.</div>');
            }
        });
    });

    $(document).on('click', '.editMedicineBtn', function () {
        $('#medicine_id').val($(this).data('id'));
        $('#name').val($(this).data('name'));
        $('#strength').val($(this).data('strength'));
        $('#morning').val($(this).data('morning'));
        $('#noon').val($(this).data('noon'));
        $('#night').val($(this).data('night'));
        $('#templeteid').val($(this).data('templeteid'));
        $('#order_type').val($(this).data('order_type'));

        $('#modalTitle').text('Edit Medicine');
        $('#medicineModal').modal('show');
    });

    $(document).on('click', '.deleteMedicineBtn', function () {
        if (!confirm('Are you sure you want to delete this medicine?')) {
            return;
        }

        var id = $(this).data('id');

        $.ajax({
            url: "{{ route('admission.freshmedicine.delete') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: id
            },
            success: function (response) {
                $('#ajaxMessage').html('<div class="alert alert-success">' + response.message + '</div>');
                loadMedicineTable();
            },
            error: function () {
                $('#ajaxMessage').html('<div class="alert alert-danger">Delete failed.</div>');
            }
        });
    });

    function loadMedicineTable() {
        $.ajax({
            url: "{{ route('admission.freshmedicineList.list') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                patientcode: $('#patientcode').val()
            },
            success: function (response) {
                $('#medicineTableArea').html(response);
            }
        });
    }

});
</script>
@endpush