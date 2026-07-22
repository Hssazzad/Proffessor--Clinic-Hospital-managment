@extends('adminlte::page')

@section('title', 'Template Investigation')

@section('content_header')
<div class="cc-appbar">
    <div class="cc-appbar-inner">
        <div class="cc-appbar-brand">
            <i class="fas fa-microscope"></i>
            <span>ClinicBilling</span>
        </div>
        <div class="cc-appbar-subtitle">Template Investigation — Manage investigation templates for diagnostic orders</div>
    </div>
</div>
@stop

@section('content')
<section class="cc-section">
    <div class="container-fluid" style="max-width:1200px;">

        {{-- Page Headline --}}
        <div class="cc-page-headline mb-3">
            <h1 class="cc-page-title">Template Investigation</h1>
            <p class="cc-page-desc">Create and manage standardized investigation templates for diagnostic workflows.</p>
        </div>

        {{-- Template Selection --}}
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="cc-card">
                    <div class="cc-card-header">
                        <i class="fas fa-file-medical-alt mr-2"></i>Select Template
                    </div>
                    <div class="cc-card-body">
                        <div class="row align-items-end">
                            <div class="col-md-6">
                                <label class="cc-label">Template <span class="text-danger">*</span></label>
                                <select id="templateid" class="form-control select2">
                                    <option value="">-- Select Template --</option>
                                    @foreach($templates as $temp)
                                        <option value="{{ $temp->templateid }}">{{ $temp->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alert Messages --}}
        <div id="alertMessage"></div>

        {{-- Main Content --}}
        <div class="row" id="investigationSection" style="display:none;">
            {{-- Left: Add Investigation Form --}}
            <div class="col-lg-5">
                <div class="cc-card cc-card-accent-blue">
                    <div class="cc-card-header cc-header-blue">
                        <i class="fas fa-plus-circle mr-2"></i>Add Investigation
                    </div>
                    <form id="investigationForm">
                        @csrf
                        <div class="cc-card-body">
                            <div class="form-group mb-2">
                                <label class="cc-label">Investigation Name <span class="text-danger">*</span></label>
                                <div class="inv-autocomplete-wrap" id="inv_name_wrap">
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="inv_name" class="form-control cc-input inv-ac-input" placeholder="Type or select investigation..." autocomplete="off">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary btn-sm inv-ac-clear" onclick="clearInvInput('inv_name','inv_name_wrap')" tabindex="-1"><i class="fas fa-times"></i></button>
                                        </div>
                                    </div>
                                    <div class="inv-ac-dropdown" id="inv_name_dropdown"></div>
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <label class="cc-label">Note</label>
                                <textarea id="inv_note" class="form-control cc-input cc-textarea" rows="3" placeholder="Optional instructions or reference range..."></textarea>
                            </div>
                        </div>
                        <div class="cc-card-footer">
                            <button type="button" id="btnAddInvestigation" class="btn cc-btn-primary btn-sm btn-block">
                                <i class="fas fa-plus mr-1"></i> Add Investigation
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Right: Investigation List --}}
            <div class="col-lg-7">
                <div class="cc-card">
                    <div class="cc-card-header cc-header-blue">
                        <i class="fas fa-list mr-2"></i>Investigation List
                        <span class="cc-list-count ml-auto" id="invCount">0</span>
                    </div>
                    <div class="cc-card-body p-0" id="invListContainer">
                        <div class="cc-empty-state" id="emptyState">
                            <div class="cc-empty-icon"><i class="fas fa-microscope"></i></div>
                            <div class="cc-empty-title">No Investigations Added</div>
                            <div class="cc-empty-text">Select a template and add diagnostic investigations to get started.</div>
                        </div>
                        <div id="invListItems" style="display:none;"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

{{-- Bottom Navigation --}}
<nav class="cc-bottom-nav">
    <a href="{{ route('billing.payment.index') }}" class="cc-nav-item">
        <i class="fas fa-file-invoice"></i>
        <span>Invoices</span>
    </a>
    <a href="{{ route('billing.paylist.index') }}" class="cc-nav-item">
        <i class="fas fa-credit-card"></i>
        <span>Payments</span>
    </a>
    <a href="{{ route('admin.dashboard') }}" class="cc-nav-item">
        <i class="fas fa-user-injured"></i>
        <span>Patients</span>
    </a>
    <a href="{{ route('templates.addmedicine') }}" class="cc-nav-item">
        <i class="fas fa-notes-medical"></i>
        <span>Forms</span>
    </a>
</nav>
@stop

@section('css')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
:root {
    --cc-bg: #faf8ff;
    --cc-primary: #0f52ba;
    --cc-primary-dark: #0b3d8a;
    --cc-primary-light: #e8edff;
    --cc-white: #ffffff;
    --cc-border: #d9d9e2;
    --cc-text: #1a1a2e;
    --cc-muted: #6b7280;
    --cc-green: #10b981;
    --cc-green-bg: #ecfdf5;
    --cc-red: #ef4444;
    --cc-red-bg: #fef2f2;
    --cc-amber: #f59e0b;
    --cc-amber-bg: #fffbeb;
    --cc-radius: 4px;
    --cc-font: 'Hanken Grotesk', system-ui, -apple-system, sans-serif;
}

body {
    background: var(--cc-bg);
    font-family: var(--cc-font);
    padding-bottom: 64px;
}

/* ── App Bar ── */
.cc-appbar {
    background: var(--cc-white);
    border-bottom: 1px solid var(--cc-border);
    padding: 12px 24px;
    margin: -8px -15px 0 -15px;
}
.cc-appbar-inner {
    display: flex;
    align-items: center;
    gap: 16px;
    max-width: 1200px;
    margin: 0 auto;
}
.cc-appbar-brand {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--cc-primary);
    letter-spacing: -0.01em;
}
.cc-appbar-brand i { font-size: 1.1rem; }
.cc-appbar-subtitle {
    font-size: 0.82rem;
    color: var(--cc-muted);
    font-weight: 400;
}

/* ── Page Headline ── */
.cc-page-headline {
    padding: 4px 0;
}
.cc-page-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--cc-text);
    margin: 0;
    letter-spacing: -0.02em;
}
.cc-page-desc {
    font-size: 0.88rem;
    color: var(--cc-muted);
    margin: 4px 0 0 0;
    font-weight: 400;
}

/* ── Section Wrapper ── */
.cc-section {
    padding-top: 12px;
}

/* ── Base Card ── */
.cc-card {
    background: var(--cc-white);
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    margin-bottom: 1.25rem;
    overflow: hidden;
}
.cc-card-accent-blue  { border-top: 3px solid var(--cc-primary); }

.cc-card-header {
    padding: 10px 16px;
    font-size: 0.82rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    border-bottom: 1px solid var(--cc-border);
    display: flex;
    align-items: center;
}
.cc-header-blue  { background: var(--cc-primary-light); color: var(--cc-primary-dark); }

.cc-card-body   { padding: 14px 16px; }
.cc-card-footer { padding: 10px 16px; background: #f8fafc; border-top: 1px solid var(--cc-border); }

/* ── List Count Badge ── */
.cc-list-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 24px;
    height: 24px;
    padding: 0 8px;
    background: var(--cc-primary);
    color: var(--cc-white);
    border-radius: var(--cc-radius);
    font-size: 0.75rem;
    font-weight: 700;
}

/* ── Labels & Inputs ── */
.cc-label {
    display: block;
    font-size: 0.76rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 3px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.cc-input, .cc-select {
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    font-size: 0.84rem;
    font-weight: 400;
    color: var(--cc-text);
    background: var(--cc-white);
    transition: border-color 0.15s;
    width: 100%;
    padding: 6px 10px;
    font-family: var(--cc-font);
}
.cc-input:focus, .cc-select:focus {
    border-color: var(--cc-primary);
    outline: none;
    box-shadow: 0 0 0 2px rgba(15,82,186,0.12);
}
.cc-textarea {
    resize: vertical;
    min-height: 70px;
    line-height: 1.6;
}
.cc-input::placeholder {
    color: #9ca3af;
    font-weight: 400;
}

/* ── Buttons ── */
.cc-btn-primary { background: var(--cc-primary); border-color: var(--cc-primary); color: var(--cc-white); font-weight: 600; }
.cc-btn-primary:hover { background: var(--cc-primary-dark); border-color: var(--cc-primary-dark); color: var(--cc-white); }
.cc-btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

/* ── Autocomplete ── */
.inv-autocomplete-wrap { position: relative; }
.inv-ac-dropdown {
    display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 9999;
    background: var(--cc-white); border: 1px solid var(--cc-border); border-top: none;
    border-radius: 0 0 var(--cc-radius) var(--cc-radius);
    max-height: 210px; overflow-y: auto;
}
.inv-ac-dropdown.show { display: block; }
.inv-ac-item {
    padding: 7px 12px; font-size: 0.82rem; cursor: pointer;
    display: flex; align-items: center; gap: 8px;
    border-bottom: 1px solid #f1f5f9; transition: background 0.1s;
}
.inv-ac-item:last-child { border-bottom: none; }
.inv-ac-item:hover, .inv-ac-item.focused { background: var(--cc-primary-light); color: var(--cc-primary); }
.inv-ac-no-result, .inv-ac-loading { padding: 10px 14px; font-size: 0.8rem; color: #9ca3af; text-align: center; }
.inv-ac-clear { border-left: 0 !important; color: #9ca3af; padding: 0 8px !important; }
.inv-ac-clear:hover { color: var(--cc-red); background: var(--cc-red-bg) !important; }
.inv-ac-item mark { background: #fef08a; color: inherit; padding: 0; font-weight: 700; }

/* ── Investigation Item Rows ── */
.cc-inv-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5fb;
    transition: background 0.12s;
}
.cc-inv-item:last-child { border-bottom: none; }
.cc-inv-item:hover { background: #f8fafc; }

.cc-inv-num {
    flex-shrink: 0;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--cc-primary-light);
    color: var(--cc-primary);
    font-size: 0.75rem;
    font-weight: 700;
    border-radius: var(--cc-radius);
    margin-top: 2px;
}

.cc-inv-body {
    flex: 1;
    min-width: 0;
}
.cc-inv-name {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--cc-text);
}
.cc-inv-note {
    font-size: 0.80rem;
    color: var(--cc-muted);
    margin-top: 2px;
    font-weight: 400;
    line-height: 1.4;
}

.cc-inv-action {
    flex-shrink: 0;
}

.cc-btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    background: var(--cc-white);
    color: #9ca3af;
    cursor: pointer;
    transition: all 0.12s;
    font-size: 0.85rem;
}
.cc-btn-icon:hover {
    background: var(--cc-red-bg);
    border-color: var(--cc-red);
    color: var(--cc-red);
}

/* ── Empty State ── */
.cc-empty-state {
    padding: 48px 24px;
    text-align: center;
}
.cc-empty-icon {
    font-size: 3rem;
    color: #d1d5db;
    margin-bottom: 12px;
}
.cc-empty-title {
    font-size: 1rem;
    font-weight: 600;
    color: #9ca3af;
    margin-bottom: 6px;
}
.cc-empty-text {
    font-size: 0.84rem;
    color: #b0b7c3;
    font-weight: 400;
}

/* ── Alerts ── */
.alert { border-radius: var(--cc-radius); font-size: 0.86rem; padding: 10px 16px; border: none; }
.cc-alert-success { background: var(--cc-green-bg); color: #065f46; border-left: 4px solid var(--cc-green); }
.cc-alert-danger  { background: var(--cc-red-bg); color: #991b1b; border-left: 4px solid var(--cc-red); }

/* ── Select2 overrides ── */
.select2-container--default .select2-selection--single {
    border: 1px solid var(--cc-border) !important;
    border-radius: var(--cc-radius) !important;
    height: 35px !important;
    font-size: 0.84rem;
    font-family: var(--cc-font);
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 33px !important;
    padding-left: 10px;
    color: var(--cc-text);
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 33px !important;
}
.select2-container--default .select2-results__option--highlighted {
    background: var(--cc-primary) !important;
}
.select2-dropdown {
    border: 1px solid var(--cc-border) !important;
    border-radius: var(--cc-radius) !important;
    font-family: var(--cc-font);
    font-size: 0.84rem;
}

/* ── Bottom Navigation ── */
.cc-bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 56px;
    background: var(--cc-white);
    border-top: 1px solid var(--cc-border);
    display: flex;
    align-items: center;
    justify-content: space-around;
    z-index: 1050;
}
.cc-nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    padding: 6px 12px;
    color: var(--cc-muted);
    text-decoration: none;
    font-size: 0.65rem;
    font-weight: 600;
    transition: color 0.15s;
    border-radius: var(--cc-radius);
}
.cc-nav-item i { font-size: 1.15rem; }
.cc-nav-item:hover { color: var(--cc-primary); text-decoration: none; }
.cc-nav-item.active { color: var(--cc-primary); }

/* ── Responsive ── */
@media (max-width: 767px) {
    .cc-page-title { font-size: 1.25rem; }
}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
/* ════════════════════════════════════════════════════════════
   INVESTIGATION AUTOCOMPLETE
════════════════════════════════════════════════════════════ */
var INVESTIGATION_LIST = [
    @if($investigation_list && count($investigation_list) > 0)
        @foreach($investigation_list as $inv)
            "{{ str_replace('"', '\\"', $inv->name) }}",
        @endforeach
    @endif
];

function highlightMatch(text, query) {
    if (!query) return escHtml(text);
    var re = new RegExp('(' + escRe(query) + ')', 'gi');
    return escHtml(text).replace(re, '<mark>$1</mark>');
}
function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function escRe(s)   { return s.replace(/[.*+?^${}()|[\]\\]/g,'\\$&'); }

function showInvDropdown(inputId, dropId, query) {
    var drop = document.getElementById(dropId);
    if (!drop) return;
    query = (query || '').trim();
    var lower = query.toLowerCase();
    var results = query.length === 0
        ? INVESTIGATION_LIST.slice(0, 30)
        : INVESTIGATION_LIST.filter(function(n) {
            return n.toLowerCase().indexOf(lower) !== -1;
          });
    if (results.length === 0) {
        drop.innerHTML = '<div class="inv-ac-no-result"><i class="fas fa-search mr-1"></i>No match — you can type freely</div>';
        drop.classList.add('show'); return;
    }
    drop.innerHTML = results.map(function(n) {
        return '<div class="inv-ac-item" data-val="' + escHtml(n) + '"><span>' + highlightMatch(n, query) + '</span></div>';
    }).join('');
    drop.querySelectorAll('.inv-ac-item').forEach(function(item) {
        item.addEventListener('mousedown', function(e) {
            e.preventDefault();
            document.getElementById(inputId).value = this.dataset.val;
            hideInvDrop(dropId);
        });
    });
    drop.classList.add('show');
}

function hideInvDrop(dropId)  { var d = document.getElementById(dropId); if (d) d.classList.remove('show'); }
function hideAllInvDrops()    { document.querySelectorAll('.inv-ac-dropdown').forEach(function(d) { d.classList.remove('show'); }); }

function initInvAC(inputId, dropId) {
    var input = document.getElementById(inputId);
    if (!input) return;
    input.addEventListener('focus',  function() { showInvDropdown(inputId, dropId, this.value); });
    input.addEventListener('input',  function() { showInvDropdown(inputId, dropId, this.value); });
    input.addEventListener('blur',   function() { setTimeout(function() { hideInvDrop(dropId); }, 180); });
    input.addEventListener('keydown', function(e) {
        var drop = document.getElementById(dropId);
        if (!drop || !drop.classList.contains('show')) return;
        var items = drop.querySelectorAll('.inv-ac-item');
        var focused = drop.querySelector('.inv-ac-item.focused');
        var idx = -1;
        items.forEach(function(it, i) { if (it === focused) idx = i; });
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (idx < items.length - 1) { if (focused) focused.classList.remove('focused'); items[idx+1].classList.add('focused'); items[idx+1].scrollIntoView({block:'nearest'}); }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (idx > 0) { if (focused) focused.classList.remove('focused'); items[idx-1].classList.add('focused'); items[idx-1].scrollIntoView({block:'nearest'}); }
        } else if (e.key === 'Enter') {
            if (focused) { e.preventDefault(); input.value = focused.dataset.val; hideInvDrop(dropId); }
        } else if (e.key === 'Escape') { hideInvDrop(dropId); }
    });
}

window.clearInvInput = function(inputId) {
    var inp = document.getElementById(inputId);
    if (inp) { inp.value = ''; inp.focus(); }
};

document.addEventListener('click', function(e) { if (!e.target.closest('.inv-autocomplete-wrap')) hideAllInvDrops(); });

/* ════════════════════════════════════════════════════════════
   MAIN APP LOGIC
════════════════════════════════════════════════════════════ */
if (typeof jQuery !== 'undefined') {
    jQuery(function($) {

        var investigationRows = [];

        $('#templateid').select2({ width: '100%' });
        initInvAC('inv_name', 'inv_name_dropdown');

        $('#templateid').on('change', function() {
            var tid = $(this).val();
            if (tid) {
                $('#investigationSection').show();
                loadList();
            } else {
                $('#investigationSection').hide();
                investigationRows = [];
            }
        });

        $('#btnAddInvestigation').on('click', function() {
            var tid = $('#templateid').val();
            var name = $('#inv_name').val().trim();
            var note = $('#inv_note').val().trim();

            if (!tid) { showAlert('Please select a template!', 'error'); return; }
            if (!name) { showAlert('Please enter investigation name!', 'error'); return; }

            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

            $.ajax({
                url: "{{ route('templates.investigation.ajax.add') }}",
                type: 'POST',
                data: { _token: "{{ csrf_token() }}", templateid: tid, name: name, note: note },
                success: function(res) {
                    if (res.ok) {
                        showAlert('Investigation added successfully.', 'success');
                        $('#inv_name').val('').focus();
                        $('#inv_note').val('');
                        loadList();
                    } else {
                        showAlert('Error: ' + (res.message || 'Could not add investigation'), 'error');
                    }
                },
                error: function() { showAlert('Server error!', 'error'); },
                complete: function() { btn.prop('disabled', false).html('<i class="fas fa-plus mr-1"></i> Add Investigation'); }
            });
        });

        function loadList() {
            var tid = $('#templateid').val();
            if (!tid) return;

            $.get("{{ route('templates.investigation.ajax.list') }}", { templateid: tid }, function(res) {
                investigationRows = res.rows || [];
                renderList();
            });
        }

        function renderList() {
            var $items = $('#invListItems');
            var $empty = $('#emptyState');
            var $count = $('#invCount');

            if (investigationRows.length === 0) {
                $empty.show();
                $items.hide();
                $count.text('0');
                return;
            }

            $empty.hide();
            $items.show();
            $count.text(investigationRows.length);

            var html = '';
            investigationRows.forEach(function(row, i) {
                var noteHtml = row.note ? '<div class="cc-inv-note">' + escHtml(row.note) + '</div>' : '';
                html += '<div class="cc-inv-item">' +
                    '<div class="cc-inv-num">' + (i + 1) + '</div>' +
                    '<div class="cc-inv-body">' +
                        '<div class="cc-inv-name">' + escHtml(row.name) + '</div>' +
                        noteHtml +
                    '</div>' +
                    '<div class="cc-inv-action">' +
                        '<button onclick="deleteInvestigation(' + row.id + ')" class="cc-btn-icon" title="Delete investigation">' +
                            '<i class="fas fa-trash"></i>' +
                        '</button>' +
                    '</div>' +
                '</div>';
            });
            $items.html(html);
        }

        window.deleteInvestigation = function(id) {
            if (!confirm('Are you sure you want to delete this investigation?')) return;

            $.ajax({
                url: '/templates/investigation/ajax/' + id,
                type: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function(res) {
                    if (res.ok || res.success) {
                        showAlert('Investigation deleted.', 'success');
                        loadList();
                    } else {
                        showAlert('Delete failed!', 'error');
                    }
                },
                error: function() { showAlert('Delete failed!', 'error'); }
            });
        };

        function showAlert(message, type) {
            var cls = type === 'success' ? 'cc-alert-success' : 'cc-alert-danger';
            var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            $('#alertMessage').html(
                '<div class="alert ' + cls + ' alert-dismissible fade show" role="alert">' +
                    '<i class="fas ' + icon + ' mr-2"></i>' + message +
                    '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>' +
                '</div>'
            );
            setTimeout(function() { $('.alert').fadeOut('slow', function() { $(this).remove(); }); }, 3000);
        }

    });
}
</script>
@stop
