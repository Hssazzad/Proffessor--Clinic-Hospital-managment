{{--
    Dual-pane account transfer picker (accounting-style), shared by the
    Add / Edit / Accounts modals on /settings/collection.

    Expected vars:
        $pickerId        unique id prefix for this picker instance
        $selectedCodes   AccountNo values already attached, in saved order
        $glTypes         acct_gltype_info rows, for the convenience filter only

    Business rule: this picker must expose exactly the accounts the Multiple
    Cash Receive dropdown exposes, so nothing here filters by gl_type.
    TransactionController::multipleCashReceive() only excludes the cash account,
    and its `exists` rule accepts any AccountNo — so an income-only list would
    be stricter than the screen that ultimately validates the posting.
    gl_type is surfaced as a manual convenience filter only.

    Submission contract (unchanged):
        <input type="hidden" name="accounts[]" value="..."> per selected row,
        emitted in panel order. syncAccounts() turns that order into sort_order.
--}}
<div class="cg-transfer" id="{{ $pickerId }}" data-cg-picker="{{ $pickerId }}">

    {{-- ================= LEFT: Available Accounts ================= --}}
    <div class="card card-primary card-outline cg-transfer-panel">
        <div class="card-header py-2">
            <h6 class="card-title mb-0 font-weight-bold">
                <i class="fas fa-list-ul mr-1"></i> Available Accounts
            </h6>
        </div>

        <div class="card-body p-2">
            <div class="form-row">
                <div class="col-12 col-sm-8 mb-2">
                    <div class="input-group input-group-sm cg-search-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                        </div>
                        <input type="text"
                               class="form-control form-control-sm cg-search border-left-0"
                               placeholder="Search by account no or name..."
                               autocomplete="off"
                               aria-label="Search accounts">
                    </div>
                </div>
                <div class="col-12 col-sm-4 mb-2">
                    <select class="form-control form-control-sm cg-type-filter" aria-label="Filter by account type">
                        <option value="">All types</option>
                        @foreach($glTypes as $glType)
                            <option value="{{ $glType->code }}">{{ $glType->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-responsive cg-scroll-area">
                <table class="table table-sm table-bordered table-hover mb-0 cg-available-table">
                    <thead class="thead-light">
                        <tr>
                            <th class="cg-col-check" style="width:34px">
                                <input type="checkbox" class="cg-check-all" title="Select all shown"
                                       aria-label="Select all shown accounts">
                            </th>
                            <th style="width:130px">Account No</th>
                            <th>Account Name</th>
                        </tr>
                    </thead>
                    <tbody class="cg-available-body"></tbody>
                </table>
            </div>

            <div class="d-flex align-items-center mt-2">
                <small class="text-muted cg-count"></small>
                <div class="ml-auto">
                    <button type="button" class="btn btn-sm btn-primary cg-add-selected">
                        <i class="fas fa-angle-double-right mr-1"></i> Add selected
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary cg-add-all">
                        <i class="fas fa-plus mr-1"></i> Add all shown
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= RIGHT: Selected Accounts ================= --}}
    <div class="card card-success card-outline cg-transfer-panel">
        <div class="card-header py-2 d-flex align-items-center">
            <h6 class="card-title mb-0 font-weight-bold">
                <i class="fas fa-check-square mr-1"></i> Selected Accounts
                <span class="badge badge-success ml-1 cg-selected-count">0</span>
            </h6>
            <button type="button" class="btn btn-sm btn-outline-danger ml-auto cg-remove-all">
                <i class="fas fa-trash mr-1"></i> Remove all
            </button>
        </div>

        <div class="card-body p-2">
            <div class="table-responsive cg-scroll-area">
                <table class="table table-sm table-bordered mb-0 cg-selected-table">
                    <thead class="thead-light">
                        <tr>
                            <th class="cg-col-handle" style="width:30px"></th>
                            <th style="width:44px">#</th>
                            <th style="width:130px">Account No</th>
                            <th>Account Name</th>
                            <th style="width:44px"></th>
                        </tr>
                    </thead>
                    <tbody class="cg-selected-body"></tbody>
                </table>
            </div>

            <div class="cg-empty-state text-center text-muted py-4">
                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                No accounts added yet. Search on the left and add.
            </div>

            <small class="text-muted d-block mt-2">
                Drag the <i class="fas fa-grip-vertical"></i> handle to reorder. This order becomes
                the row order on Multiple Cash Receive.
            </small>

            <div class="text-danger small mt-1 cg-required-error" style="display:none">
                <i class="fas fa-exclamation-circle mr-1"></i> Please add at least one account.
            </div>
        </div>

        {{-- Submission payload: rendered server-side so the form works without JS,
         and rebuilt by JS on every add/remove/reorder. Hidden inputs are
         emitted in panel order, which is what syncAccounts() reads as
         sort_order. --}}
        <div class="cg-selected-codes">
            @foreach((array) $selectedCodes as $cgCode)
                @if($cgCode !== '' && $cgCode !== null)
                    <input type="hidden" name="accounts[]" value="{{ $cgCode }}">
                @endif
            @endforeach
        </div>
    </div>
</div>