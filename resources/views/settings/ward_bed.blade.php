@extends('adminlte::page')

@section('title', 'Ward & Bed Management')

@section('content_header')
    <h1><i class="fas fa-bed"></i> Ward & Bed Management</h1>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        {{-- Left: Add New Bed Form --}}
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Add New Bed</h3>
                </div>
                <form action="{{ route('settings.wardbed.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        {{-- Bed Name Added Here --}}
                        <div class="form-group">
                            <label>Bed Name <span class="text-danger">*</span></label>
                            <input type="text" name="bed_name" class="form-control" placeholder="e.g. VIP Bed 1 / Bed-A" required>
                        </div>
                        <div class="form-group">
                            <label>Bed Number <span class="text-danger">*</span></label>
                            <input type="text" name="bed_number" class="form-control" placeholder="e.g. W-105" required>
                        </div>
                        <div class="form-group">
                            <label>Bed Type / Ward <span class="text-danger">*</span></label>
                            <select name="bed_type" class="form-control" required>
                                <option value="">-- Select --</option>
                                <option value="General Ward">General Ward</option>
                                <option value="ICU">ICU</option>
                                <option value="CCU">CCU</option>
                                <option value="HDU">HDU</option>
                                <option value="Private Cabin">Private Cabin</option>
                                <option value="Emergency">Emergency</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Price Per Day (BDT)</label>
                            <input type="number" name="price_per_day" class="form-control" placeholder="e.g. 1500" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Save Bed
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Right: Beds List --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Beds</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-bordered table-sm mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Bed Name</th> {{-- Table Header Added --}}
                                <th>Bed Number</th>
                                <th>Type / Ward</th>
                                <th>Price/Day</th>
                                <th>Status</th>
                                <th style="width:120px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($beds as $bed)
                                <tr>
                                    <td>{{ $bed->id }}</td>
                                    <td>{{ $bed->bed_name }}</td> {{-- Display Bed Name --}}
                                    <td>{{ $bed->bed_number }}</td>
                                    <td>{{ $bed->bed_type }}</td>
                                    <td>{{ $bed->price_per_day ? number_format($bed->price_per_day, 0) . ' BDT' : '—' }}</td>
                                    <td>
                                        @php
                                            $badge = match($bed->status) {
                                                'vacant' => 'badge-success',
                                                'occupied' => 'badge-danger',
                                                'maintenance' => 'badge-warning',
                                                default => 'badge-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $badge }}">
                                            {{ ucfirst($bed->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-warning btn-sm editBtn"
                                            data-id="{{ $bed->id }}"
                                            data-bed_name="{{ $bed->bed_name }}" {{-- Data Attribute Added --}}
                                            data-bed_number="{{ $bed->bed_number }}"
                                            data-bed_type="{{ $bed->bed_type }}"
                                            data-price_per_day="{{ $bed->price_per_day }}"
                                            data-status="{{ $bed->status }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('settings.wardbed.destroy', $bed->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this bed?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No beds found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $beds->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editBedModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editBedForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">Edit Bed</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        {{-- Bed Name Input in Edit Modal --}}
                        <div class="form-group">
                            <label>Bed Name <span class="text-danger">*</span></label>
                            <input type="text" name="bed_name" id="edit_bed_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Bed Number <span class="text-danger">*</span></label>
                            <input type="text" name="bed_number" id="edit_bed_number" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Bed Type / Ward <span class="text-danger">*</span></label>
                            <select name="bed_type" id="edit_bed_type" class="form-control" required>
                                <option value="General Ward">General Ward</option>
                                <option value="ICU">ICU</option>
                                <option value="CCU">CCU</option>
                                <option value="HDU">HDU</option>
                                <option value="Private Cabin">Private Cabin</option>
                                <option value="Emergency">Emergency</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Price Per Day (BDT)</label>
                            <input type="number" name="price_per_day" id="edit_price_per_day" class="form-control" step="0.01" min="0">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" id="edit_status" class="form-control">
                                <option value="vacant">Vacant</option>
                                <option value="occupied">Occupied</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
$(function () {
    $('.editBtn').on('click', function () {
        var id = $(this).data('id');
        $('#edit_id').val(id);
        $('#edit_bed_name').val($(this).data('bed_name')); // Bed Name JS handler
        $('#edit_bed_number').val($(this).data('bed_number'));
        $('#edit_bed_type').val($(this).data('bed_type'));
        $('#edit_price_per_day').val($(this).data('price_per_day'));
        $('#edit_status').val($(this).data('status'));
        $('#editBedForm').attr('action', '{{ url('settings/wardbed/update') }}' + '/' + id);
        $('#editBedModal').modal('show');
    });
});
</script>
@stop