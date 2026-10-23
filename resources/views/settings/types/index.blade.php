@extends('adminlte::page')

@section('title', 'Manage Medicine Types')

@section('content')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-0" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-0" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> Please fix the errors below.
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- Add Type Card --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h5 class="card-title mb-0 font-weight-bold">
                <i class="fas fa-plus-circle mr-1"></i> Add New Medicine Type
            </h5>
        </div>
        <form method="POST" action="{{ route('settings.types.store') }}">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fas fa-tag mr-1 text-muted"></i> Type Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="type_name"
                                   class="form-control @error('type_name') is-invalid @enderror"
                                   value="{{ old('type_name') }}" placeholder="e.g. Oral Tablet">
                            @error('type_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary font-weight-bold">
                            <i class="fas fa-save mr-1"></i> Save Type
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Types List Card --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h5 class="card-title mb-0 font-weight-bold">
                <i class="fas fa-list mr-1"></i> All Medicine Types
            </h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Type Name</th>
                        <th style="width:100px">Sub Types</th>
                        <th style="width:100px">Status</th>
                        <th style="width:220px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($types as $type)
                        <tr>
                            <td>{{ $type->id }}</td>
                            <td class="font-weight-bold">{{ $type->type_name }}</td>
                            <td>
                                <a href="#" class="badge badge-info subtype-count"
                                   data-type-id="{{ $type->id }}"
                                   data-toggle="modal" data-target="#subTypeModal">
                                    {{ $type->sub_types_count ?? 0 }} Sub Type(s)
                                </a>
                            </td>
                            <td>
                                @if($type->active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info manage-subtypes"
                                        data-type-id="{{ $type->id }}"
                                        data-type-name="{{ $type->type_name }}"
                                        data-toggle="modal" data-target="#subTypeModal"
                                        title="Manage Sub Types">
                                    <i class="fas fa-sitemap"></i> Sub Types
                                </button>
                                <button class="btn btn-sm btn-warning edit-type"
                                        data-id="{{ $type->id }}"
                                        data-name="{{ $type->type_name }}"
                                        data-active="{{ $type->active }}"
                                        data-toggle="modal" data-target="#editTypeModal"
                                        title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('settings.types.destroy', $type->id) }}"
                                      style="display:inline-block"
                                      onsubmit="return confirm('Delete this Type and all its Sub Types?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No types found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($types->hasPages())
            <div class="card-footer">
                {{ $types->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ===================== Edit Type Modal ===================== --}}
<div class="modal fade" id="editTypeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="POST" id="editTypeForm">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit mr-1"></i> Edit Medicine Type</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Type Name <span class="text-danger">*</span></label>
                        <input type="text" name="type_name" id="editTypeName"
                               class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Status</label>
                        <select name="active" id="editTypeActive" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ===================== Sub Type Management Modal ===================== --}}
<div class="modal fade" id="subTypeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-sitemap mr-1"></i> Sub Types: <span id="subTypeModalTitle"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="subTypeParentId">

                {{-- Add Sub Type --}}
                <div class="input-group mb-3">
                    <input type="text" id="newSubTypeName" class="form-control"
                           placeholder="Enter new Sub Type name...">
                    <div class="input-group-append">
                        <button id="addSubTypeBtn" class="btn btn-primary" type="button">
                            <i class="fas fa-plus mr-1"></i> Add
                        </button>
                    </div>
                </div>

                {{-- Sub Types List --}}
                <table class="table table-bordered table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Name</th>
                            <th style="width:80px">Status</th>
                            <th style="width:160px">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="subTypeTableBody">
                        <tr><td colspan="4" class="text-center text-muted py-3">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- ===================== Edit Sub Type Modal ===================== --}}
<div class="modal fade" id="editSubTypeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="editSubTypeForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit mr-1"></i> Edit Sub Type</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editSubTypeId">
                    <div class="form-group">
                        <label class="font-weight-bold">Name <span class="text-danger">*</span></label>
                        <input type="text" id="editSubTypeName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Status</label>
                        <select id="editSubTypeStatus" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Update</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
$(function () {

    // ========== Edit Type Modal ==========
    $('.edit-type').on('click', function () {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var active = $(this).data('active');
        $('#editTypeForm').attr('action', '{{ route('settings.types.update', ':id') }}'.replace(':id', id));
        $('#editTypeName').val(name);
        $('#editTypeActive').val(active);
    });

    // ========== Sub Type Management ==========
    function loadSubTypes(typeId) {
        if (!typeId) return;
        $('#subTypeTableBody').html('<tr><td colspan="4" class="text-center text-muted py-3">Loading...</td></tr>');
        $.get('{{ route('settings.types.sub-types.ajax', ':typeId') }}'.replace(':typeId', typeId), function (subtypes) {
            if (!subtypes.length) {
                $('#subTypeTableBody').html('<tr><td colspan="4" class="text-center text-muted py-3">No Sub Types found.</td></tr>');
                return;
            }
            var html = '';
            $.each(subtypes, function (i, st) {
                html += '<tr>';
                html += '<td>' + (i + 1) + '</td>';
                html += '<td>' + $('<span>').text(st.subtype_name).html() + '</td>';
                html += '<td>' + (st.active ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>') + '</td>';
                html += '<td>';
                html += '<button class="btn btn-sm btn-warning edit-subtype mr-1" data-id="' + st.id + '" data-name="' + $('<span>').text(st.subtype_name).html() + '" data-status="' + st.active + '"><i class="fas fa-edit"></i></button>';
                html += '<button class="btn btn-sm btn-danger delete-subtype" data-id="' + st.id + '" data-name="' + $('<span>').text(st.subtype_name).html() + '"><i class="fas fa-trash"></i></button>';
                html += '</td>';
                html += '</tr>';
            });
            $('#subTypeTableBody').html(html);
        });
    }

    $('.manage-subtypes, .subtype-count').on('click', function () {
        var typeId = $(this).data('type-id');
        var typeName = $(this).data('type-name') || $(this).closest('tr').find('td:nth-child(2)').text().trim();
        $('#subTypeParentId').val(typeId);
        $('#subTypeModalTitle').text(typeName);
        loadSubTypes(typeId);
    });

    $('#addSubTypeBtn').on('click', function () {
        var typeId = $('#subTypeParentId').val();
        var name = $('#newSubTypeName').val().trim();
        if (!name) { alert('Please enter a Sub Type name.'); return; }

        $.ajax({
            url: '{{ route('settings.types.sub-types.store') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                medicine_type_id: typeId,
                subtype_name: name
            },
            success: function () {
                $('#newSubTypeName').val('');
                loadSubTypes(typeId);
                toastr?.success('Sub Type added successfully.') || alert('Sub Type added.');
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message || 'Error adding Sub Type.';
                alert(msg);
            }
        });
    });

    // Edit Sub Type
    $(document).on('click', '.edit-subtype', function () {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var status = $(this).data('status');
        $('#editSubTypeId').val(id);
        $('#editSubTypeName').val(name);
        $('#editSubTypeStatus').val(status);
        $('#editSubTypeModal').modal('show');
    });

    $('#editSubTypeForm').on('submit', function (e) {
        e.preventDefault();
        var id = $('#editSubTypeId').val();
        var name = $('#editSubTypeName').val().trim();
        var status = $('#editSubTypeStatus').val();
        if (!name) { alert('Please enter a name.'); return; }

        $.ajax({
            url: '{{ route('settings.types.sub-types.update', ':subId') }}'.replace(':subId', id),
            method: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                subtype_name: name,
                active: status
            },
            success: function () {
                $('#editSubTypeModal').modal('hide');
                loadSubTypes($('#subTypeParentId').val());
                toastr?.success('Sub Type updated.') || alert('Sub Type updated.');
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message || 'Error updating Sub Type.');
            }
        });
    });

    // Delete Sub Type
    $(document).on('click', '.delete-subtype', function () {
        if (!confirm('Delete this Sub Type?')) return;
        var id = $(this).data('id');
        var typeId = $('#subTypeParentId').val();

        $.ajax({
            url: '{{ route('settings.types.sub-types.destroy', ':delId') }}'.replace(':delId', id),
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function () {
                loadSubTypes(typeId);
                toastr?.success('Sub Type deleted.') || alert('Sub Type deleted.');
            },
            error: function () {
                alert('Error deleting Sub Type.');
            }
        });
    });

    // Refresh sub type count badges after modal close
    $('#subTypeModal').on('hidden.bs.modal', function () {
        location.reload();
    });
});
</script>
@endsection
