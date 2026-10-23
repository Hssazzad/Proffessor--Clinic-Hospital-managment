@extends('adminlte::page')

@section('title', 'Bill Types Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark" style="font-size: 18px; font-weight: 600;">Bill Types Management</h1>
        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addModal" style="background-color: #00bfa5; border: none;">
            <i class="fas fa-plus"></i> Add New Bill Type
        </button>
    </div>
@stop

@section('content')
<div class="card" style="border-top: 3px solid #00bfa5;">
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-bordered text-center" style="font-size: 13px;">
            <thead style="background-color: #f4f6f9;">
                <tr>
                    <th style="width: 50px;">SL</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Color / Icon</th>
                    <th>Config</th>
                    <th>Sort</th>
                    <th>Status</th>
                    <th style="width: 120px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($billTypes as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left font-weight-bold">{{ $item->name }}</td>
                    <td><span class="badge badge-light">{{ $item->slug }}</span></td>
                    <td>
                        <span style="color: {{ $item->color }}; font-size: 16px;">
                            <i class="fas {{ $item->icon }}"></i>
                        </span>
                    </td>
                    <td style="font-size: 11px;">
                        @if($item->requires_doctor) <span class="badge badge-info">Doc</span> @endif
                        @if($item->requires_category) <span class="badge badge-success">Cat</span> @endif
                        @if($item->free_text_items) <span class="badge badge-warning">Free Text</span> @endif
                    </td>
                    <td>{{ $item->sort_order }}</td>
                    <td>
                        @if($item->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-xs btn-info btnEdit" 
                            data-id="{{ $item->id }}"
                            data-name="{{ $item->name }}"
                            data-slug="{{ $item->slug }}"
                            data-icon="{{ $item->icon }}"
                            data-color="{{ $item->color }}"
                            data-main_code="{{ $item->main_code }}"
                            data-sort_order="{{ $item->sort_order }}"
                            data-req_doc="{{ $item->requires_doctor }}"
                            data-req_cat="{{ $item->requires_category }}"
                            data-free_text="{{ $item->free_text_items }}"
                            data-active="{{ $item->is_active }}">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-xs btn-danger btnDelete" data-id="{{ $item->id }}">
                            <i class="fas fa-trash"></i> Del
                        </button>
                    </td>
                </tr>
                @endforeach
                @if($billTypes->isEmpty())
                <tr>
                    <td colspan="8" class="text-center text-muted p-3">No Bill Types Found</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

{{-- ADD MODAL --}}
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="addForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header" style="background-color: #00bfa5; color: #fff; padding: 10px 15px;">
                    <h5 class="modal-title" style="font-size: 15px;">Add New Bill Type</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="font-size: 13px;">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Slug</label>
                            <input type="text" name="slug" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Icon (e.g. fa-user-md)</label>
                            <input type="text" name="icon" class="form-control form-control-sm" value="fa-file-invoice">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Color Code</label>
                            <input type="color" name="color" class="form-control form-control-sm" style="height: 31px; padding:0 2px;" value="#1a3a5c">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Sort Order</label>
                            <input type="number" name="sort_order" class="form-control form-control-sm" value="0">
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Main Code (Optional)</label>
                            <input type="number" name="main_code" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="row border-top pt-2 mt-1">
                        <div class="col-md-3"><label><input type="checkbox" name="requires_doctor" value="1"> Req. Doctor</label></div>
                        <div class="col-md-3"><label><input type="checkbox" name="requires_category" value="1"> Req. Category</label></div>
                        <div class="col-md-3"><label><input type="checkbox" name="free_text_items" value="1" checked> Free Text</label></div>
                        <div class="col-md-3"><label><input type="checkbox" name="is_active" value="1" checked> Active</label></div>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 10px;">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-sm btn-primary" id="btnSaveAdd">Save Data</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- EDIT MODAL --}}
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="editForm">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_id" name="id">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #1a3a5c; color: #fff; padding: 10px 15px;">
                    <h5 class="modal-title" style="font-size: 15px;">Edit Bill Type</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="font-size: 13px;">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Slug</label>
                            <input type="text" name="slug" id="edit_slug" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Icon</label>
                            <input type="text" name="icon" id="edit_icon" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Color Code</label>
                            <input type="color" name="color" id="edit_color" class="form-control form-control-sm" style="height: 31px; padding:0 2px;">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Sort Order</label>
                            <input type="number" name="sort_order" id="edit_sort" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Main Code</label>
                            <input type="number" name="main_code" id="edit_main_code" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="row border-top pt-2 mt-1">
                        <div class="col-md-3"><label><input type="checkbox" name="requires_doctor" id="edit_req_doc" value="1"> Req. Doctor</label></div>
                        <div class="col-md-3"><label><input type="checkbox" name="requires_category" id="edit_req_cat" value="1"> Req. Category</label></div>
                        <div class="col-md-3"><label><input type="checkbox" name="free_text_items" id="edit_free_text" value="1"> Free Text</label></div>
                        <div class="col-md-3"><label><input type="checkbox" name="is_active" id="edit_active" value="1"> Active</label></div>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 10px;">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-sm btn-primary" id="btnSaveEdit">Update Data</button>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(document).ready(function() {
    // CSRF Token Setup for AJAX
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    toastr.options = { positionClass: 'toast-top-right', timeOut: 2000 };

    // --- ADD REQUEST ---
    $('#addForm').submit(function(e) {
        e.preventDefault();
        let btn = $('#btnSaveAdd');
        btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: "{{ route('settings.billtypes.store') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(response) {
                toastr.success('Added Successfully!');
                setTimeout(() => location.reload(), 1000); // ??? ????? ??? ???? ??????? ??? ???
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Error occurred');
                btn.prop('disabled', false).text('Save Data');
            }
        });
    });

    // --- POPULATE EDIT MODAL ---
    $('.btnEdit').click(function() {
        let btn = $(this);
        $('#edit_id').val(btn.data('id'));
        $('#edit_name').val(btn.data('name'));
        $('#edit_slug').val(btn.data('slug'));
        $('#edit_icon').val(btn.data('icon'));
        $('#edit_color').val(btn.data('color'));
        $('#edit_sort').val(btn.data('sort_order'));
        $('#edit_main_code').val(btn.data('main_code'));

        $('#edit_req_doc').prop('checked', btn.data('req_doc') == 1);
        $('#edit_req_cat').prop('checked', btn.data('req_cat') == 1);
        $('#edit_free_text').prop('checked', btn.data('free_text') == 1);
        $('#edit_active').prop('checked', btn.data('active') == 1);

        $('#editModal').modal('show');
    });

    // --- EDIT REQUEST ---
    $('#editForm').submit(function(e) {
        e.preventDefault();
        let id = $('#edit_id').val();
        let btn = $('#btnSaveEdit');
        btn.prop('disabled', true).text('Updating...');

        $.ajax({
            url: "/settings/billtypes/update/" + id,
            type: "PUT",
            data: $(this).serialize(),
            success: function(response) {
                toastr.success('Updated Successfully!');
                setTimeout(() => location.reload(), 1000);
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Error occurred');
                btn.prop('disabled', false).text('Update Data');
            }
        });
    });

    // --- DELETE REQUEST ---
    $('.btnDelete').click(function() {
        let id = $(this).data('id');
        if(confirm('Are you sure you want to delete this bill type?')) {
            $.ajax({
                url: "/settings/billtypes/delete/" + id,
                type: "DELETE",
                success: function(response) {
                    toastr.success('Deleted Successfully!');
                    setTimeout(() => location.reload(), 1000);
                },
                error: function(xhr) {
                    toastr.error('Error deleting data');
                }
            });
        }
    });
});
</script>
@stop