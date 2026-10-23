{{-- resources/views/accounting/account_list.blade.php --}}
@extends('adminlte::page')

@section('title', 'Account List')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @include('accounting.account_list_table', ['rs_result' => $rs_result])
            </div>
        </div>
    </div>

    <!-- Edit Account Modal -->
    <div class="modal fade" id="editAccountModal" tabindex="-1" role="dialog" aria-labelledby="editAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="editAccountForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editAccountModalLabel">Edit Account</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_id" name="id">
                        
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Accounts Type :</label>
                            <div class="col-sm-8">
                                <input type="text" id="edit_gl_type" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Group Name :</label>
                            <div class="col-sm-8">
                                <input type="text" id="edit_gl_group" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Sub Group :</label>
                            <div class="col-sm-8">
                                <input type="text" id="edit_gl_sub_group" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Account No :</label>
                            <div class="col-sm-8">
                                <input type="text" id="edit_AccountNo" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Account Name :</label>
                            <div class="col-sm-8">
                                <input type="text" id="edit_title" name="title" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer text-center" style="display: block;">
                        <button type="submit" class="btn btn-primary">Save Now</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
$(document).ready(function() {
    // Open modal and populate data
    $(document).on('click', '.edit-btn', function(e) {
        e.preventDefault();
        
        var id = $(this).data('id');
        var gl_type = $(this).data('gl_type');
        var gl_group = $(this).data('gl_group');
        var gl_sub_group = $(this).data('gl_sub_group');
        var accountno = $(this).data('accountno');
        var title = $(this).data('title');
        
        $('#edit_id').val(id);
        $('#edit_gl_type').val(gl_type);
        $('#edit_gl_group').val(gl_group);
        $('#edit_gl_sub_group').val(gl_sub_group);
        $('#edit_AccountNo').val(accountno);
        $('#edit_title').val(title);
        
        $('#editAccountModal').modal('show');
    });

    // Handle AJAX form submission
    $('#editAccountForm').on('submit', function(e) {
        e.preventDefault();
        var id = $('#edit_id').val();
        var formData = $(this).serialize();
        
        $.ajax({
            url: '/Accounting/account/' + id,
            type: 'POST', // Method overriding is handled by @method('PUT')
            data: formData,
            success: function(response) {
                $('#editAccountModal').modal('hide');
                // You can update the table row directly or reload the page
                // For now, let's reload to ensure the table reflects changes correctly
                location.reload();
            },
            error: function(xhr) {
                alert('Validation error or something went wrong!');
            }
        });
    });
});
</script>
@endpush