@extends('adminlte::page')

@section('title', 'Workflow Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">⚙️ Treatment Workflow Settings</h1>
        <button class="btn btn-primary shadow" data-toggle="modal" data-target="#addTypeModal">
            <i class="fas fa-plus-circle"></i> Create New Pathway
        </button>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success shadow-sm border-0">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger shadow-sm border-0">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Pathway Name</th>
                            <th class="text-center">Pre-Surg</th>
                            <th class="text-center">Post-Surg</th>
                            <th class="text-center">Round</th>
                            <th class="text-center">Fresh</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($types as $type)
                            <tr>
                                <td class="align-middle font-weight-bold text-primary">{{ $type->name }}</td>
                                
                                {{-- Stage 2: Pre-Surgery --}}
                                <td class="text-center">
                                    @if($type->skip_pre_surgery)
                                        <i class="fas fa-times-circle text-danger" title="Skipped"></i>
                                    @else
                                        <i class="fas fa-check-circle text-success" title="Active"></i>
                                    @endif
                                </td>

                                {{-- Stage 3: Post-Surgery --}}
                                <td class="text-center">
                                    @if($type->skip_post_surgery)
                                        <i class="fas fa-times-circle text-danger" title="Skipped"></i>
                                    @else
                                        <i class="fas fa-check-circle text-success" title="Active"></i>
                                    @endif
                                </td>

                                {{-- Stage 4: Round Patient --}}
                                <td class="text-center">
                                    @if($type->skip_round_patient)
                                        <i class="fas fa-times-circle text-danger" title="Skipped"></i>
                                    @else
                                        <i class="fas fa-check-circle text-success" title="Active"></i>
                                    @endif
                                </td>

                                {{-- Stage 5: Fresh Prescription --}}
                                <td class="text-center">
                                    @if($type->skip_fresh_prescription)
                                        <i class="fas fa-times-circle text-danger" title="Skipped"></i>
                                    @else
                                        <i class="fas fa-check-circle text-success" title="Active"></i>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <span class="badge badge-{{ $type->status ? 'success' : 'secondary' }} px-2">
                                        {{ $type->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="align-middle text-right">
                                    <button class="btn btn-sm btn-info edit-btn" 
                                        data-id="{{ $type->id }}" 
                                        data-name="{{ $type->name }}" 
                                        data-pre="{{ $type->skip_pre_surgery }}"
                                        data-post="{{ $type->skip_post_surgery }}"
                                        data-round="{{ $type->skip_round_patient }}"
                                        data-fresh="{{ $type->skip_fresh_prescription }}"
                                        data-status="{{ $type->status }}"
                                        data-toggle="modal" data-target="#editTypeModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('settings.treatment_types.destroy', $type->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this pathway?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4">No workflows defined.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add Modal --}}
    <div class="modal fade" id="addTypeModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('settings.treatment_types.store') }}" method="POST">
                @csrf
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Configure New Pathway</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Pathway Name (যেমন: Medical, Surgical)</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter name" required>
                        </div>
                        <h6 class="font-weight-bold text-muted mt-4">Select Stages to SKIP (বাদ দিতে টিক দিন):</h6>
                        <hr class="mt-1">
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" name="skip_pre_surgery" value="1" class="custom-control-input" id="skipPreAdd">
                            <label class="custom-control-label" for="skipPreAdd">Skip Pre-Surgery (Stage 2)</label>
                        </div>
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" name="skip_post_surgery" value="1" class="custom-control-input" id="skipPostAdd">
                            <label class="custom-control-label" for="skipPostAdd">Skip Post-Surgery (Stage 3)</label>
                        </div>
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" name="skip_round_patient" value="1" class="custom-control-input" id="skipRoundAdd">
                            <label class="custom-control-label" for="skipRoundAdd">Skip Round Patient (Stage 4)</label>
                        </div>
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" name="skip_fresh_prescription" value="1" class="custom-control-input" id="skipFreshAdd">
                            <label class="custom-control-label" for="skipFreshAdd">Skip Fresh Prescription (Stage 5)</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary btn-block">Create Pathway</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editTypeModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Update Pathway Settings</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Pathway Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <h6 class="font-weight-bold text-muted mt-4">Select Stages to SKIP:</h6>
                        <hr class="mt-1">
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" name="skip_pre_surgery" value="1" class="custom-control-input" id="edit_pre">
                            <label class="custom-control-label" for="edit_pre">Skip Pre-Surgery</label>
                        </div>
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" name="skip_post_surgery" value="1" class="custom-control-input" id="edit_post">
                            <label class="custom-control-label" for="edit_post">Skip Post-Surgery</label>
                        </div>
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" name="skip_round_patient" value="1" class="custom-control-input" id="edit_round">
                            <label class="custom-control-label" for="edit_round">Skip Round Patient</label>
                        </div>
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" name="skip_fresh_prescription" value="1" class="custom-control-input" id="edit_fresh">
                            <label class="custom-control-label" for="edit_fresh">Skip Fresh Prescription</label>
                        </div>
                        <div class="form-group mt-3">
                            <label>Status</label>
                            <select name="status" id="edit_status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-info text-white btn-block">Update Pathway</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.edit-btn').on('click', function() {
            let id = $(this).data('id');
            $('#editForm').attr('action', '/settings/treatment-types/update/' + id);
            $('#edit_name').val($(this).data('name'));
            $('#edit_status').val($(this).data('status'));
            
            // Checkboxes set করার লজিক
            $('#edit_pre').prop('checked', $(this).data('pre') == 1);
            $('#edit_post').prop('checked', $(this).data('post') == 1);
            $('#edit_round').prop('checked', $(this).data('round') == 1);
            $('#edit_fresh').prop('checked', $(this).data('fresh') == 1);
        });
    });
</script>
@stop
