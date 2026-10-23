@extends('adminlte::page')

@section('title', 'Chamber Fee Configuration')

@section('content_header')
    <h1><i class="fas fa-list"></i> Chamber Fee Configuration <small class="text-muted">চেম্বার ফি কনফিগারেশন</small></h1>
@endsection

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-money-bill-wave"></i> Chamber Fees</h3>
        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#cfcModal" onclick="cfcResetForm()">
            <i class="fas fa-plus"></i> Add Chamber
        </button>
    </div>

    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4 ml-auto">
                <input type="text" id="cfcSearch" class="form-control" placeholder="Search chamber name...">
            </div>
        </div>

        <table class="table table-bordered table-striped" id="cfcTable">
            <thead>
                <tr>
                    <th>Chamber Name</th>
                    <th>First Fee</th>
                    <th>Follow-up</th>
                    <th>Expired</th>
                    <th>Days</th>
                    <th>Status</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($configs as $c)
                <tr data-id="{{ $c->id }}">
                    <td>{{ $c->chamber_name }}</td>
                    <td>{{ number_format($c->first_visit_fee, 0) }} Tk</td>
                    <td>{{ number_format($c->followup_fee, 0) }} Tk</td>
                    <td>{{ number_format($c->expired_fee, 0) }} Tk</td>
                    <td>{{ $c->followup_days }}</td>
                    <td>
                        <span class="badge badge-{{ $c->status ? 'success' : 'danger' }} cfc-status-toggle"
                              data-id="{{ $c->id }}" style="cursor:pointer">
                            {{ $c->status ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-right">
                        <button class="btn btn-info btn-xs cfc-edit-btn"
                            data-id="{{ $c->id }}"
                            data-name="{{ $c->chamber_name }}"
                            data-first="{{ $c->first_visit_fee }}"
                            data-followup="{{ $c->followup_fee }}"
                            data-expired="{{ $c->expired_fee }}"
                            data-days="{{ $c->followup_days }}"
                            data-status="{{ $c->status }}">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        @if(RoleCheck(99))
                        <button class="btn btn-danger btn-xs cfc-delete-btn" data-id="{{ $c->id }}">
                            <i class="fas fa-trash"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">No chamber fee configs found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="d-flex justify-content-end">
            {{ $configs->links() }}
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="cfcModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="cfcForm">
                <input type="hidden" id="cfc_id">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="cfcModalTitle"><i class="fas fa-plus"></i> Add Chamber Fee</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Chamber Name <small class="text-muted">চেম্বারের নাম</small></label>
                        <input type="text" id="cfc_chamber_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>First Visit Fee <small class="text-muted">প্রথম ভিজিট ফি</small></label>
                        <input type="number" step="0.01" id="cfc_first_visit_fee" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Follow-up Fee <small class="text-muted">ফলোআপ ফি</small></label>
                        <input type="number" step="0.01" id="cfc_followup_fee" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Expired Visit Fee <small class="text-muted">মেয়াদোত্তীর্ণ ফি</small></label>
                        <input type="number" step="0.01" id="cfc_expired_fee" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Follow-up Days <small class="text-muted">ফলোআপ দিন</small></label>
                        <input type="number" id="cfc_followup_days" class="form-control" value="90" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <div class="icheck-primary d-inline mr-3">
                            <input type="radio" name="cfc_status" id="cfc_status_active" value="1" checked>
                            <label for="cfc_status_active">Active</label>
                        </div>
                        <div class="icheck-primary d-inline">
                            <input type="radio" name="cfc_status" id="cfc_status_inactive" value="0">
                            <label for="cfc_status_inactive">Inactive</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(function () {
    function cfcResetForm() {
        $('#cfcForm')[0].reset();
        $('#cfc_id').val('');
        $('#cfc_status_active').prop('checked', true);
        $('#cfcModalTitle').html('<i class="fas fa-plus"></i> Add Chamber Fee');
    }
    window.cfcResetForm = cfcResetForm;

    $('.cfc-edit-btn').on('click', function () {
        $('#cfc_id').val($(this).data('id'));
        $('#cfc_chamber_name').val($(this).data('name'));
        $('#cfc_first_visit_fee').val($(this).data('first'));
        $('#cfc_followup_fee').val($(this).data('followup'));
        $('#cfc_expired_fee').val($(this).data('expired'));
        $('#cfc_followup_days').val($(this).data('days'));
        if ($(this).data('status') == 1) {
            $('#cfc_status_active').prop('checked', true);
        } else {
            $('#cfc_status_inactive').prop('checked', true);
        }
        $('#cfcModalTitle').html('<i class="fas fa-edit"></i> Edit Chamber Fee');
        $('#cfcModal').modal('show');
    });

    $('#cfcForm').on('submit', function (e) {
        e.preventDefault();
        const id = $('#cfc_id').val();
        const payload = {
            _token: '{{ csrf_token() }}',
            chamber_name: $('#cfc_chamber_name').val(),
            first_visit_fee: $('#cfc_first_visit_fee').val(),
            followup_fee: $('#cfc_followup_fee').val(),
            expired_fee: $('#cfc_expired_fee').val(),
            followup_days: $('#cfc_followup_days').val(),
            status: $('input[name=cfc_status]:checked').val(),
        };

        const url = id ? `/settings/chamber-fee/update/${id}` : '/settings/chamber-fee/store';
        if (id) payload._method = 'PUT';

        $.post(url, payload)
            .done(function () { location.reload(); })
            .fail(function (xhr) {
                let msg = 'Something went wrong.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors)[0][0];
                }
                alert(msg);
            });
    });

    $('.cfc-status-toggle').on('click', function () {
        const id = $(this).data('id');
        $.ajax({
            url: `/settings/chamber-fee/toggle-status/${id}`,
            method: 'PATCH',
            data: { _token: '{{ csrf_token() }}' },
            success: function () { location.reload(); }
        });
    });

    $('.cfc-delete-btn').on('click', function () {
        if (!confirm('Delete this chamber fee config?')) return;
        const id = $(this).data('id');
        $.ajax({
            url: `/settings/chamber-fee/delete/${id}`,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function () { location.reload(); }
        });
    });

    // Client-side search filter
    $('#cfcSearch').on('keyup', function () {
        const val = $(this).val().toLowerCase();
        $('#cfcTable tbody tr').filter(function () {
            $(this).toggle($(this).find('td:first').text().toLowerCase().indexOf(val) > -1);
        });
    });
});
</script>
@endsection