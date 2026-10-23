@forelse($templateMedicines ?? [] as $m)
<tr id="medicineRow{{ $m->id }}">
    <td>{{ $m->templeteid }}</td>
    <td>{{ $m->name }}</td>
    <td>{{ $m->strength }}</td>
    <td>{{ $m->morning }}</td>
    <td>{{ $m->noon }}</td>
    <td>{{ $m->night }}</td>
    <td>{{ $m->route }}</td>
    <td>{{ $m->timing }}</td>
    <td>{{ $m->instruction }}</td>
    <td>
        <button type="button" class="btn btn-warning btn-xs"
                data-toggle="modal"
                data-target="#editMedicineModal{{ $m->id }}">
            <i class="fas fa-edit"></i>
        </button>

        <button type="button" class="btn btn-danger btn-xs ajaxDeleteMedicine"
                data-id="{{ $m->id }}">
            <i class="fas fa-trash"></i>
        </button>
    </td>
</tr>

<div class="modal fade" id="editMedicineModal{{ $m->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form class="ajaxUpdateMedicineForm"
                  action="{{ route('admissionmedicine.update', $m->id) }}"
                  method="POST">
                @csrf
                @method('PUT')

                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Edit Medicine</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Template ID</label>
                            <input type="text" name="templeteid" value="{{ $m->templeteid }}" class="form-control">
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Medicine</label>
                            <input type="text" name="name" value="{{ $m->name }}" class="form-control" required>
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Strength</label>
                            <input type="text" name="strength" value="{{ $m->strength }}" class="form-control">
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Morning</label>
                            <input type="text" name="morning" value="{{ $m->morning }}" class="form-control">
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Noon</label>
                            <input type="text" name="noon" value="{{ $m->noon }}" class="form-control">
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Night</label>
                            <input type="text" name="night" value="{{ $m->night }}" class="form-control">
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Route</label>
                            <input type="text" name="route" value="{{ $m->route }}" class="form-control">
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Timing</label>
                            <input type="text" name="timing" value="{{ $m->timing }}" class="form-control">
                        </div>

                        <div class="col-md-12 form-group">
                            <label>Instruction</label>
                            <input type="text" name="instruction" value="{{ $m->instruction }}" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@empty
<tr>
    <td colspan="10" class="text-center text-muted">No medicine found</td>
</tr>
@endforelse