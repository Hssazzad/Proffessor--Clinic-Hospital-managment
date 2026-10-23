{{-- UNIFIED MASTER-ADD MODAL: Company / Medicine Type / Dosage Form / Sub Type.
     Driven by data attributes on the "Add" buttons (data-master-kind). --}}
<div class="modal fade" id="masterAddModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header pm-modal-header">
                <h5 class="modal-title" id="masterAddTitle">Add New</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="masterAddAlert" class="alert alert-danger py-2" style="display:none;"></div>

                {{-- Sub type only: shows the parent Medicine Type --}}
                <div class="form-group" id="masterAddParentGroup" style="display:none;">
                    <label>Medicine Type</label>
                    <input type="text" id="masterAddParentName" class="form-control bg-light" readonly>
                </div>

                <div class="form-group mb-0">
                    <label for="masterAddName" id="masterAddNameLabel">Name <span class="text-danger">*</span></label>
                    <input type="text" id="masterAddName" class="form-control" placeholder="" maxlength="150">
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary pm-btn-footer" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn pm-btn-footer pm-save-btn" id="btnMasterAddSave">
                    Save
                </button>
            </div>
        </div>
    </div>
</div>
