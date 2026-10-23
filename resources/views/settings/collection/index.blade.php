@extends('adminlte::page')

@section('title', 'Manage Collection Groups')

{{-- Dual-pane account picker styling. Bootstrap 4 / AdminLTE only (card,
     table, thead-light, btn-sm) — no new library. Scoped to .cg-transfer so
     no other screen is affected. --}}
@push('css')
<style>
    .cg-transfer .cg-transfer-panel .card-body { min-height: 0; }

    /* Fixed-height scroll area with a sticky header. */
    .cg-transfer .cg-scroll-area {
        max-height: 380px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        background: #fff;
    }
    .cg-transfer .cg-scroll-area .thead-light th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f8f9fa;
    }

    .cg-transfer .cg-available-table td,
    .cg-transfer .cg-selected-table td { vertical-align: middle; padding: 0.3rem 0.5rem; }
    .cg-transfer .cg-available-table .cg-row-code { font-weight: 700; color: #007bff; white-space: nowrap; }
    .cg-transfer .cg-selected-table .cg-row-code { font-weight: 700; color: #007bff; white-space: nowrap; }

    .cg-transfer .cg-available-table tbody tr { cursor: pointer; }
    .cg-transfer .cg-available-table tbody tr.cg-row-disabled { cursor: not-allowed; opacity: 0.55; }
    .cg-transfer .cg-row-index { color: #6c757d; }

    /* jQuery UI sortable handle on the selected table. */
    .cg-transfer .cg-col-handle { cursor: move; color: #adb5bd; text-align: center; }
    .cg-transfer .cg-selected-body tr.ui-sortable-helper { background: #e9ecef; }
    .cg-transfer .cg-selected-body tr.ui-sortable-helper td { height: 34px; }
    .cg-transfer .cg-selected-table tbody.ui-sortable { position: relative; }

    .cg-transfer .cg-count { color: #6c757d; }
    .cg-transfer .cg-empty-state i { color: #ced4da; }
    .cg-transfer .cg-search-group .input-group-text,
    .cg-transfer .cg-search-group .form-control { height: 31px; }

    @media (max-width: 991.98px) {
        /* Stack the panels vertically on narrow screens. */
        .cg-transfer .cg-transfer-panel { margin-bottom: 0.75rem; }
        .cg-transfer .cg-scroll-area { max-height: 260px; }
    }
</style>
@endpush

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-layer-group"></i> Collection Groups <small class="text-muted">কালেকশন গ্রুপ</small></h1>
        <button class="btn btn-primary font-weight-bold" data-toggle="modal" data-target="#addGroupModal">
            <i class="fas fa-plus-circle"></i> Add New Group
        </button>
    </div>
@stop

@section('content')
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

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h5 class="card-title mb-0 font-weight-bold">
                <i class="fas fa-list mr-1"></i> All Collection Groups
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:110px">Code</th>
                            <th>Group Name</th>
                            <th style="width:110px">Accounts</th>
                            <th style="width:110px">Status</th>
                            <th style="width:220px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groups as $group)
                            <tr>
                                <td class="font-weight-bold text-primary">{{ $group->code }}</td>
                                <td class="font-weight-bold">{{ $group->name }}</td>
                                <td>
                                    <span class="badge badge-info cg-count"
                                          data-accounts="{{ $group->accounts->pluck('account_code')->implode(',') }}">
                                        {{ $group->accounts->count() }} Account(s)
                                    </span>
                                    @if(!empty($missingCodes[$group->id]))
                                        <span class="badge badge-warning ml-1 cg-count"
                                              data-accounts="{{ $missingCodes[$group->id] }}">
                                            <i class="fas fa-exclamation-triangle"></i> {{ count($missingCodes[$group->id]) }} Missing
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($group->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info cg-manage"
                                            data-id="{{ $group->id }}"
                                            data-code="{{ $group->code }}"
                                            data-name="{{ $group->name }}"
                                            data-active="{{ $group->is_active ? 1 : 0 }}"
                                            data-accounts="{{ $group->accounts->pluck('account_code')->implode(',') }}"
                                            data-toggle="modal" data-target="#groupModal"
                                            title="Manage Accounts">
                                        <i class="fas fa-sitemap"></i> Accounts
                                    </button>
                                    <button class="btn btn-sm btn-warning cg-edit"
                                            data-id="{{ $group->id }}"
                                            data-code="{{ $group->code }}"
                                            data-name="{{ $group->name }}"
                                            data-active="{{ $group->is_active ? 1 : 0 }}"
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('settings.collection.destroy', $group->id) }}"
                                          style="display:inline-block"
                                          onsubmit="return confirm('Delete this Collection Group?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No collection groups found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

{{-- ===================== Add Group Modal ===================== --}}
<div class="modal fade" id="addGroupModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <form method="POST" action="{{ route('settings.collection.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white"><i class="fas fa-plus-circle mr-1"></i> Add Collection Group</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                       value="{{ old('code') }}" placeholder="e.g. 001" maxlength="50" required>
                                @error('code') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Group Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" placeholder="e.g. OPD Counter Collection" maxlength="150" required>
                                @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">Status</label>
                                <select name="is_active" class="form-control">
                                    <option value="1" selected>Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <label class="font-weight-bold d-block">
                        <i class="fas fa-money-bill-wave mr-1 text-muted"></i> Accounts in this Group <span class="text-danger">*</span>
                    </label>
                    <small class="text-muted d-block mb-2">
                        These heads appear as rows on Multiple Cash Receive. Rows left at 0 are not saved.
                    </small>
                    @include('settings.collection.partials.account-picker', ['pickerId' => 'cgAddAccounts', 'selectedCodes' => old('accounts', [])])
                    @error('accounts.*') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Save Group
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ===================== Edit / Manage Accounts Modal ===================== --}}
<div class="modal fade" id="groupModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <form method="POST" id="cgEditForm">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white"><i class="fas fa-edit mr-1"></i> Edit Collection Group</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" id="cgEditCode" class="form-control" maxlength="50" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Group Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="cgEditName" class="form-control" maxlength="150" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">Status</label>
                                <select name="is_active" id="cgEditActive" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <label class="font-weight-bold d-block">
                        <i class="fas fa-money-bill-wave mr-1 text-muted"></i> Accounts in this Group <span class="text-danger">*</span>
                    </label>
                    <small class="text-muted d-block mb-2">
                        Removing an account here only affects the receive screen — saved vouchers are unchanged.
                    </small>
                    @include('settings.collection.partials.account-picker', ['pickerId' => 'cgEditAccounts', 'selectedCodes' => []])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info font-weight-bold text-white">
                        <i class="fas fa-save mr-1"></i> Update Group
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ===================== Accounts Detail Modal ===================== --}}
<div class="modal fade" id="cgAccountsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary">
                <h5 class="modal-title text-white" id="cgAccountsTitle"><i class="fas fa-sitemap mr-1"></i> Group Accounts</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:60px">#</th>
                            <th style="width:130px">Account No</th>
                            <th>Account Title</th>
                        </tr>
                    </thead>
                    <tbody id="cgAccountsBody"></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@section('js')
{{-- Built here rather than inline in @json(): Blade's @json() splits its
     argument on commas, so a closure with commas inside it gets truncated.
     This is the single account payload the picker filters client-side. --}}
@php
    $cgAccountIndexJson = $accounts->mapWithKeys(function ($account) {
        return [$account->AccountNo => [
            'code'    => $account->AccountNo,
            'name'    => $account->title,
            'gl_type' => $account->gl_type,
        ]];
    });
@endphp

{{-- jQuery UI is already used across the accounting screens (multiplecashreceive,
     cashpayment, bankpayment all load 1.13.2 the same way). Reused here for
     drag-to-reorder instead of adding another library. --}}
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
$(function () {
    /**
     * Dual-pane account transfer picker.
     *
     * One JSON payload (cgAccountIndex) is emitted once per page; all filtering
     * happens client-side. `accounts` is the SAME list Multiple Cash Receive
     * offers, so nothing here silently restricts what can be attached.
     */
    const cgAccountIndex = @json($cgAccountIndexJson);

    function CgTransfer($root, initialCodes) {
        var self = this;
        var $root = $root;
        var $available = $root.find('.cg-available-body');
        var $selected = $root.find('.cg-selected-body');
        var $search = $root.find('.cg-search');
        var $typeFilter = $root.find('.cg-type-filter');
        var $checkAll = $root.find('.cg-check-all');

        // Ordered list of chosen AccountNo values — the submission order.
        this.codes = [];

        function byCode(code) {
            return cgAccountIndex[code] || null;
        }

        function isAttached(code) {
            return self.codes.indexOf(code) !== -1;
        }

        function matches(account, needle, glType) {
            if (glType !== '' && String(account.gl_type) !== glType) {
                return false;
            }
            if (needle === '') {
                return true;
            }
            // Case-insensitive partial match on AccountNo AND account name.
            return (account.code + ' ' + account.name).toLowerCase().indexOf(needle) !== -1;
        }

        function currentFilter() {
            return {
                needle: $.trim($search.val() || '').toLowerCase(),
                glType: $typeFilter.val() || ''
            };
        }

        function visibleAccounts() {
            var f = currentFilter();
            return Object.keys(cgAccountIndex)
                .map(function (code) { return cgAccountIndex[code]; })
                .filter(function (account) { return matches(account, f.needle, f.glType); });
        }

        function renderAvailable() {
            var rows = visibleAccounts();
            var shown = 0;
            $available.empty();

            rows.forEach(function (account) {
                var attached = isAttached(account.code);
                if (!attached) {
                    shown++;
                }

                var $tr = $('<tr>')
                    .attr('data-code', account.code)
                    .toggleClass('cg-row-disabled', attached)
                    .append(
                        $('<td>').append(
                            $('<input type="checkbox">')
                                .attr('data-code', account.code)
                                .prop('checked', false)
                                .prop('disabled', attached)
                                .addClass('cg-row-check')
                        )
                    )
                    .append($('<td class="cg-row-code"></td>').text(account.code))
                    .append($('<td></td>').text(account.name));

                if (attached) {
                    $tr.find('td:last-child').append(
                        $('<span class="badge badge-secondary ml-1"></span>').text('already added')
                    );
                }

                $available.append($tr);
            });

            if (rows.length === 0) {
                $available.append(
                    $('<tr>').append(
                        $('<td colspan="3" class="text-center text-muted py-3"></td>').text('No matching accounts')
                    )
                );
            }

            var total = Object.keys(cgAccountIndex).length;
            $root.find('.cg-count').text('Showing ' + shown + ' of ' + total);
            syncCheckAll(rows);
        }

        function syncCheckAll(rows) {
            var selectable = rows.filter(function (a) { return !isAttached(a.code); });
            var checked = selectable.filter(function (a) {
                return $available.find('.cg-row-check[data-code="' + a.code + '"]').prop('checked');
            });

            $checkAll.prop('checked', selectable.length > 0 && checked.length === selectable.length);
            $checkAll.prop('indeterminate', checked.length > 0 && checked.length < selectable.length);
        }

        function addCodes(codes) {
            codes.forEach(function (code) {
                if (!isAttached(code) && byCode(code)) {
                    self.codes.push(code);
                }
            });
            renderSelected();
            renderAvailable();
        }

        function renderSelected() {
            $selected.empty();
            var known = 0;

            self.codes.forEach(function (code, index) {
                var account = byCode(code);
                // A code with no account row no longer resolves in the chart of
                // accounts — keep it in place and flag it as Missing rather than
                // dropping it silently on the next save.
                var isMissing = account === null;
                if (!isMissing) {
                    known++;
                }

                $selected.append(
                    $('<tr>').attr('data-code', code).append(
                        $('<td class="cg-col-handle"></td>').append($('<i class="fas fa-grip-vertical"></i>')),
                        $('<td class="cg-row-index"></td>').text(index + 1),
                        $('<td class="cg-row-code"></td>').text(code),
                        $('<td></td>').append(
                            $('<span></span>').text(account ? account.name : 'No longer in chart of accounts')
                        ).append(
                            isMissing
                                ? $('<span class="badge badge-danger cg-missing-badge ml-1"></span>').text('Missing')
                                : $('<span></span>')
                        ),
                        $('<td class="text-right"></td>').append(
                            $('<button type="button" class="btn btn-sm btn-outline-danger cg-remove" title="Remove"></button>')
                                .append($('<i class="fas fa-times"></i>'))
                        )
                    )
                );
            });

            var total = self.codes.length;
            $root.find('.cg-selected-count').text(total);
            $root.find('.cg-empty-state').toggle(total === 0);
            $root.find('.cg-remove-all').prop('disabled', total === 0);

            if (known < total) {
                $root.find('.cg-selected-count')
                    .attr('title', (total - known) + ' account(s) no longer exist in the chart of accounts');
            } else {
                $root.find('.cg-selected-count').removeAttr('title');
            }

            syncPayload();
        }

        // The submission payload. Rebuilt from the rendered row order, so a
        // drag-reorder or a remove is reflected on the next submit. This is the
        // only thing the backend reads — `accounts[]` in panel order, which
        // syncAccounts() turns into sort_order.
        function syncPayload() {
            var ordered = $selected.find('tr').map(function () {
                return $(this).attr('data-code');
            }).get();

            self.codes = ordered;

            var $hidden = $root.find('.cg-selected-codes').empty();
            ordered.forEach(function (code) {
                $hidden.append($('<input type="hidden" name="accounts[]">').val(code));
            });

            // Serial column must follow the current order.
            $selected.find('tr').each(function (index) {
                $(this).find('.cg-row-index').text(index + 1);
            });

            $root.find('.cg-selected-count').text(ordered.length);
        }

        function removeCode(code) {
            self.codes = self.codes.filter(function (c) { return c !== code; });
            renderSelected();
            renderAvailable();
        }

        // ---- events ----
        $search.on('input', renderAvailable);

        $typeFilter.on('change', renderAvailable);

        $checkAll.on('change', function () {
            var want = $(this).prop('checked');
            $available.find('.cg-row-check').each(function () {
                if (!$(this).prop('disabled')) {
                    $(this).prop('checked', want);
                }
            });
            syncCheckAll(visibleAccounts());
        });

        $available.on('change', '.cg-row-check', function () {
            var want = $(this).prop('checked');
            $available.find('.cg-row-check').each(function () {
                if (!$(this).prop('disabled')) {
                    $(this).prop('checked', want);
                }
            });
            syncCheckAll(visibleAccounts());
        });

        // Row click toggles the checkbox; double-click adds straight away.
        $available.on('click', 'tr', function (e) {
            if ($(e.target).is('input')) {
                return;
            }
            var $check = $(this).find('.cg-row-check');
            if ($check.prop('disabled')) {
                return;
            }
            $check.prop('checked', !$check.prop('checked'));
            syncCheckAll(visibleAccounts());
        });

        $available.on('dblclick', 'tr', function () {
            var $check = $(this).find('.cg-row-check');
            if ($check.prop('disabled')) {
                return;
            }
            addCodes([$(this).attr('data-code')]);
        });

        $root.on('click', '.cg-add-selected', function () {
            var codes = [];
            $available.find('.cg-row-check').each(function () {
                if ($(this).prop('checked') && !$(this).prop('disabled')) {
                    codes.push($(this).attr('data-code'));
                }
            });
            if (codes.length === 0) {
                return;
            }
            addCodes(codes);
            $available.find('.cg-row-check').prop('checked', false);
            syncCheckAll(visibleAccounts());
        });

        $root.on('click', '.cg-add-all', function () {
            var codes = visibleAccounts()
                .map(function (a) { return a.code; })
                .filter(function (code) { return !isAttached(code); });
            addCodes(codes);
        });

        $root.on('click', '.cg-remove', function () {
            removeCode($(this).closest('tr').attr('data-code'));
        });

        $root.on('click', '.cg-remove-all', function () {
            self.codes = [];
            renderSelected();
            renderAvailable();
        });

        // Drag to reorder; jQuery UI is already a project dependency.
        if ($.fn.sortable) {
            $selected.sortable({
                handle: '.cg-col-handle',
                axis: 'y',
                items: '> tr',
                tolerance: 'pointer',
                update: renderSelected,
                helper: function (event, tr) {
                    const $tr = $(tr).clone();
                    $tr.children().each(function (index) {
                        $(this).attr('data-col', index);
                    });
                    $tr.find('tbody, td').removeAttr('rowspan');
                    $tr.children().each(function () {
                        $(this).width($(tr).children().eq($(this).data('col')).width());
                    });
                    $tr.css('cursor', 'move').addClass('cg-dragging');
                    return $tr;
                }
            });
        }

        // At least one account is required, matching the backend rule.
        $root.closest('form').on('submit', function (e) {
            if (self.codes.length === 0) {
                e.preventDefault();
                $root.find('.cg-required-error').show();
                $root.find('.cg-transfer-panel').last().animate(
                    { boxShadow: ['0 0 0 3px rgba(220,53,69,0.35)', '0 0 0 3px rgba(220,53,69,0)'] },
                    900
                );
            }
        });

        $root.on('click', '.cg-add-selected, .cg-add-all, .cg-remove, .cg-remove-all', function () {
            $root.find('.cg-required-error').hide();
        });

        this.setCodes = function (codes) {
            self.codes = (codes || []).filter(function (code) {
                return code !== null && code !== undefined && code !== '';
            });
            renderSelected();
            renderAvailable();
            $root.find('.cg-required-error').hide();
        };

        this.getCodes = function () {
            return self.codes.slice();
        };

        this.focusSearch = function () {
            $search.trigger('focus');
        };

        renderSelected();
        renderAvailable();
    }

    const cgPickers = {};

    function cgPicker(name) {
        const $root = $('[data-cg-picker="' + name + '"]');
        if (!$root.length) {
            return null;
        }
        if (!cgPickers[name]) {
            cgPickers[name] = new CgTransfer($root, []);
        }
        return cgPickers[name];
    }

    // ---- modal wiring ----
    $('#addGroupModal').on('shown.bs.modal', function () {
        const picker = cgPicker('cgAddAccounts');
        if (!picker) {
            return;
        }
        @if($errors->any())
            // Keep the redisplayed old input after a validation failure.
            picker.setCodes(@json(array_values((array) old('accounts'))));
        @else
            picker.setCodes([]);
        @endif
        picker.focusSearch();
    });

    function cgOpenGroupModal($btn) {
        $('#cgEditForm').attr('action', '/settings/collection/update/' + $btn.data('id'));
        $('#cgEditCode').val($btn.data('code'));
        $('#cgEditName').val($btn.data('name'));
        $('#cgEditActive').val(String($btn.data('active')));

        const codes = ($btn.attr('data-accounts') || '')
            .split(',')
            .map(function (c) { return c.trim(); })
            .filter(function (c) { return c !== ''; });

        const picker = cgPicker('cgEditAccounts');
        if (picker) {
            picker.setCodes(codes);
        }

        $('#groupModal').modal('show');
        if (picker) {
            picker.focusSearch();
        }
    }

    // Manage Accounts (opens the edit modal, pre-filled with the group's list)
    $('.cg-manage').on('click', function () {
        cgOpenGroupModal($(this));
    });

    // Edit (code / name / status only — account list untouched)
    $('.cg-edit').on('click', function () {
        cgOpenGroupModal($(this));
    });

    // Account count badge → read-only breakdown. Resolves names from the shared
    // cgAccountIndex (there is no <option> list any more), so a code that no
    // longer exists in the chart of accounts shows the red Missing treatment.
    $('.cg-count').on('click', function () {
        var raw = $(this).attr('data-accounts') || '';
        var isMissingBadge = $(this).hasClass('badge-warning');

        var codes = raw.split(',')
            .map(function (c) { return c.trim(); })
            .filter(function (c) { return c !== ''; });

        var $body = $('#cgAccountsBody').empty();
        $('#cgAccountsTitle').text(isMissingBadge ? 'Missing Accounts' : 'Group Accounts');

        if (codes.length === 0) {
            $body.append('<tr><td colspan="3" class="text-center text-muted py-3">No accounts linked to this group.</td></tr>');
        } else {
            codes.forEach(function (code, i) {
                var account = cgAccountIndex[code] || null;

                $body.append(
                    $('<tr>').append(
                        $('<td>').text(i + 1),
                        $('<td>').addClass('font-weight-bold text-primary').text(code),
                        $('<td>').addClass(account ? '' : 'text-danger')
                            .append($('<span></span>').text(account ? account.name : 'No longer in chart of accounts'))
                            .append(account
                                ? $('<span></span>')
                                : $('<span class="badge badge-danger ml-1"></span>').text('Missing'))
                    )
                );
            });
        }

        $('#cgAccountsModal').modal('show');
    });

    // Reopening the Add modal starts a clean form, but never wipe the redisplayed
    // old input after a validation failure — the user needs to see what they ticked.
    const cgKeepAddSelection = @json((bool) ($errors->any() || old('_token')));

    $('#addGroupModal').on('shown.bs.modal', function () {
        if (!cgKeepAddSelection) {
            $('#cgAddAccounts').val([]).trigger('change.select2');
        }
    });
});
</script>
@endsection