{{-- EDIT MEDICINE MODAL (same field order as the create form) --}}
<div class="modal fade pm-compact" id="editMedicineModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="editMedicineForm" method="POST" action="#">
                @csrf
                @method('PUT')
                <div class="modal-header pm-modal-header">
                    <h5 class="modal-title">Edit Medicine</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row pm-form-row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_company_id">Company <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select name="company_id" id="edit_company_id"
                                            class="form-control select2edit" data-placeholder="-- Select Company --">
                                        <option value=""></option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-primary btn-open-type-modal"
                                                data-master-kind="company"
                                                data-target-select="#edit_company_id"
                                                title="Add New Company">
    Add
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_medicine_type_id">Medicine Type <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select name="medicine_type_id" id="edit_medicine_type_id"
                                            class="form-control select2edit" data-placeholder="-- Select Medicine Type --">
                                        <option value=""></option>
                                        @foreach($medicineTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->type_name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-primary btn-open-type-modal"
                                                data-master-kind="type"
                                                data-target-select="#edit_medicine_type_id"
                                                title="Add New Medicine Type">
    Add
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_medicine_subtype_id">
                                    Sub Medicine Type <small class="text-muted font-weight-normal">(optional)</small>
                                </label>
                                <div class="input-group">
                                    <select name="medicine_subtype_id" id="edit_medicine_subtype_id"
                                            class="form-control select2edit" data-placeholder="-- Select Sub Type --">
                                        <option value=""></option>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-primary btn-open-type-modal"
                                                data-master-kind="subtype"
                                                data-target-select="#edit_medicine_subtype_id"
                                                data-type-select="#edit_medicine_type_id"
                                                title="Add New Sub Medicine Type">
    Add
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_dosage_form_id">Dosage Form <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select name="dosage_form_id" id="edit_dosage_form_id"
                                            class="form-control select2edit" data-placeholder="-- Select Dosage Form --">
                                        <option value=""></option>
                                        @foreach($dosageForms as $form)
                                            <option value="{{ $form->id }}">{{ $form->dosage_name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-primary btn-open-type-modal"
                                                data-master-kind="dosage"
                                                data-target-select="#edit_dosage_form_id"
                                                title="Add New Dosage Form">
    Add
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_medicine_name">Medicine Name <span class="text-danger">*</span></label>
                                <input type="text" name="medicine_name" id="edit_medicine_name" class="form-control" maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_strength">Strength <small class="text-muted">(optional)</small></label>
                                <input type="text" name="strength" id="edit_strength" class="form-control" maxlength="50">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_note">Note <small class="text-muted">(shows on prescription)</small></label>
                                <input type="text" name="note" id="edit_note" class="form-control" maxlength="255">
                            </div>
                        </div>
                        @if($hasGenericName)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_generic_name">Generic Name</label>
                                    <input type="text" name="generic_name" id="edit_generic_name" class="form-control" maxlength="255">
                                </div>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_status">Status</label>
                                <select name="status" id="edit_status" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>⭐ Commonly Used Medicine</label>
                                <div class="d-flex align-items-center mt-1">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="commonly_used"
                                               id="edit_commonly_used" value="1" role="switch">
                                        <label class="form-check-label text-muted small" for="edit_commonly_used" id="edit_commonly_used_label">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-outline-secondary pm-btn-footer" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn pm-btn-footer pm-save-btn">Update Medicine</button>
                </div>
            </form>
        </div>
    </div>
</div>
