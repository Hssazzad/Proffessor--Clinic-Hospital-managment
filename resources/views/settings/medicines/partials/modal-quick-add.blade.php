{{-- QUICK ADD MODAL (Bootstrap modal, AJAX save, no page reload) --}}
<div class="modal fade pm-compact" id="quickAddModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header pm-modal-header">
                <h5 class="modal-title">Quick Add Medicine</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="quickAddAlert" class="alert alert-danger py-2" style="display:none;font-size:13px;"></div>

                {{-- Company --}}
                <div class="form-group">
                    <label for="qa_company_id">Company <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select id="qa_company_id" class="form-control">
                            <option value="">-- Select Company --</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-primary btn-open-type-modal"
                                    data-master-kind="company"
                                    data-target-select="#qa_company_id" title="Add New Company">
                                Add
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Medicine Type --}}
                <div class="form-group">
                    <label for="qa_medicine_type_id">Medicine Type <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select id="qa_medicine_type_id" class="form-control">
                            <option value="">-- Select Medicine Type --</option>
                            @foreach($medicineTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->type_name }}</option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-primary btn-open-type-modal"
                                    data-master-kind="type"
                                    data-target-select="#qa_medicine_type_id" title="Add New Medicine Type">
                                Add
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Sub Medicine Type --}}
                <div class="form-group" id="qa_subTypeWrapper" style="display:none;">
                    <label for="qa_medicine_subtype_id">
                        Sub Medicine Type <small class="text-muted font-weight-normal">(optional)</small>
                    </label>
                    <div class="input-group">
                        <select id="qa_medicine_subtype_id" class="form-control">
                            <option value="">-- Select Sub Type --</option>
                        </select>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-primary btn-open-type-modal"
                                    data-master-kind="subtype"
                                    data-target-select="#qa_medicine_subtype_id"
                                    data-type-select="#qa_medicine_type_id" title="Add New Sub Medicine Type">
                                Add
                            </button>
                        </div>
                    </div>
                    <p id="qa_subTypeEmptyHint" class="form-text text-muted mb-0" style="display:none;">
                        No sub types exist for this type — skip this field.
                    </p>
                </div>

                {{-- Dosage Form --}}
                <div class="form-group">
                    <label for="qa_dosage_form_id">Dosage Form <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select id="qa_dosage_form_id" class="form-control">
                            <option value="">-- Capsule / Tablet / Injection --</option>
                            @foreach($dosageForms as $form)
                                <option value="{{ $form->id }}">{{ $form->dosage_name }}</option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-primary btn-open-type-modal"
                                    data-master-kind="dosage"
                                    data-target-select="#qa_dosage_form_id" title="Add New Dosage Form">
                                Add
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Medicine Name --}}
                <div class="form-group">
                    <label for="qa_medicine_name">Medicine Name <span class="text-danger">*</span></label>
                    <input type="text" id="qa_medicine_name" class="form-control"
                           placeholder="e.g. Amoxicillin" maxlength="255" autocomplete="off">
                </div>

                {{-- Strength (optional) --}}
                <div class="form-group">
                    <label for="qa_strength">Strength <small class="text-muted font-weight-normal">(optional)</small></label>
                    <input type="text" id="qa_strength" class="form-control"
                           placeholder="e.g. 500 mg" maxlength="50" autocomplete="off">
                </div>

                {{-- Note (optional, shows on prescription) --}}
                <div class="form-group mb-0">
                    <label for="qa_note">Note <small class="text-muted font-weight-normal">(shows on prescription)</small></label>
                    <input type="text" id="qa_note" class="form-control"
                           placeholder="e.g. Take after meal" maxlength="255" autocomplete="off">
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" id="qaCancelBtn" class="btn btn-outline-secondary pm-btn-footer" data-dismiss="modal">
                    Cancel
                </button>
                <button type="button" id="btnQuickAddSave" class="btn pm-btn-footer pm-save-btn">
                    Save &amp; Use
                </button>
            </div>
        </div>
    </div>
</div>
