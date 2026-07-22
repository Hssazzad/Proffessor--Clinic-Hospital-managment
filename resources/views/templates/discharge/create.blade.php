@extends('adminlte::page')

@section('title', 'Template Discharge')

@section('content')
<section class="cc-page">

    {{-- Alert Messages --}}
    <div id="alertMessage"></div>

    {{-- Template Selection --}}
    <div class="cc-card cc-card-accent">
        <div class="cc-card-header">
            <i class="fas fa-file-medical-alt mr-2"></i>Select Template
        </div>
        <div class="cc-card-body">
            <label class="cc-label">Template <span class="text-danger">*</span></label>
            <select id="templateid" class="form-control select2">
                <option value="">-- Select Template --</option>
                @foreach($templates as $temp)
                    <option value="{{ $temp->templateid }}">{{ $temp->title }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Discharge Form --}}
    <div class="cc-card cc-card-accent" id="formSection" style="display:none;">
        <div class="cc-card-header">
            <i class="fas fa-file-alt mr-2"></i>Discharge Summary
        </div>
        <form id="dischargeForm">
            @csrf
            <div class="cc-card-body">
                <div class="cc-field">
                    <label class="cc-label">Treatment Given <span class="text-danger">*</span></label>
                    <textarea id="treatment_given" class="cc-input cc-textarea" rows="4" placeholder="Describe the treatment provided during admission..."></textarea>
                </div>
                <div class="cc-field">
                    <label class="cc-label">Condition at Discharge <span class="text-danger">*</span></label>
                    <textarea id="condition" class="cc-input cc-textarea" rows="3" placeholder="e.g. Clinically stable, improved, referred..."></textarea>
                </div>
                <div class="cc-field">
                    <label class="cc-label">Follow-up Advice</label>
                    <textarea id="follow_up" class="cc-input cc-textarea" rows="3" placeholder="e.g. Visit OPD after 7 days, medication course..."></textarea>
                </div>
            </div>
            <div class="cc-card-footer">
                <button type="button" id="btnSaveDischarge" class="cc-btn-primary cc-btn-block">
                    <i class="fas fa-plus mr-1"></i> Save Discharge Note
                </button>
            </div>
        </form>
    </div>

    {{-- Saved Discharge Preview --}}
    <div class="cc-card" id="savedSection" style="display:none;">
        <div class="cc-card-header">
            <i class="fas fa-check-circle mr-2"></i>Saved Discharge Summary
        </div>
        <div class="cc-card-body" id="dischargePreview">
            {{-- Empty state shown initially --}}
            <div class="cc-empty-state" id="emptyState">
                <div class="cc-empty-icon"><i class="fas fa-notes-medical"></i></div>
                <div class="cc-empty-title">No Discharge Saved</div>
                <div class="cc-empty-text">Select a template, fill in the discharge details, and save to see the summary here.</div>
            </div>
            {{-- Loaded content --}}
            <div id="savedContent" style="display:none;">
                <div class="cc-saved-field">
                    <label class="cc-label">Treatment Given</label>
                    <div class="cc-saved-text" id="savedTreatment">—</div>
                </div>
                <div class="cc-saved-field">
                    <label class="cc-label">Condition at Discharge</label>
                    <div class="cc-saved-text" id="savedCondition">—</div>
                </div>
                <div class="cc-saved-field">
                    <label class="cc-label">Follow-up Advice</label>
                    <div class="cc-saved-text" id="savedFollowUp">—</div>
                </div>
            </div>
        </div>
    </div>

</section>

{{-- Bottom Navigation --}}
<nav class="cc-bottom-nav">
    <a href="{{ route('admin.dashboard') }}" class="cc-nav-item">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <a href="{{ route('admin.dashboard') }}" class="cc-nav-item">
        <i class="fas fa-user-injured"></i>
        <span>Patients</span>
    </a>
    <a href="{{ route('templates.addmedicine') }}" class="cc-nav-item">
        <i class="fas fa-file-alt"></i>
        <span>Forms</span>
    </a>
    <a href="{{ route('admin.dashboard') }}" class="cc-nav-item">
        <i class="fas fa-user"></i>
        <span>Profile</span>
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
    --cc-nav-height: 56px;
}

body {
    background: var(--cc-bg);
    font-family: var(--cc-font);
    padding-bottom: var(--cc-nav-height);
    padding-top: 0;
    margin: 0;
}
.content-wrapper, .content-header, .main-header {
    background: transparent !important;
    padding: 0 !important;
    margin: 0 !important;
    border: none !important;
    box-shadow: none !important;
    min-height: 0 !important;
}
.content-header { display: none !important; }
.main-header { display: none !important; }

/* ── Top App Bar ── */
.cc-topbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: var(--cc-topbar-height);
    background: var(--cc-white);
    border-bottom: 1px solid var(--cc-border);
    display: flex;
    align-items: center;
    padding: 0 12px;
    z-index: 1050;
    gap: 8px;
}
.cc-topbar-btn {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: transparent;
    color: var(--cc-muted);
    font-size: 1.15rem;
    border-radius: var(--cc-radius);
    cursor: pointer;
    transition: background 0.12s, color 0.12s;
    flex-shrink: 0;
}
.cc-topbar-btn:hover {
    background: var(--cc-primary-light);
    color: var(--cc-primary);
}
.cc-topbar-title {
    flex: 1;
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--cc-text);
    margin: 0;
    letter-spacing: -0.01em;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.cc-topbar-actions {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-shrink: 0;
}

/* ── Page Content ── */
.cc-page {
    padding: 16px 14px 8px;
    max-width: 600px;
    margin: 0 auto;
}

/* ── Base Card ── */
.cc-card {
    background: var(--cc-white);
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    margin-bottom: 14px;
    overflow: hidden;
}
.cc-card-accent {
    border-top: 3px solid var(--cc-primary);
}

.cc-card-header {
    padding: 10px 14px;
    font-size: 0.80rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    border-bottom: 1px solid var(--cc-border);
    display: flex;
    align-items: center;
    color: var(--cc-text);
}
.cc-card-body {
    padding: 14px;
}
.cc-card-footer {
    padding: 12px 14px;
    background: #f8fafc;
    border-top: 1px solid var(--cc-border);
}

/* ── Field Group ── */
.cc-field {
    margin-bottom: 14px;
}
.cc-field:last-child {
    margin-bottom: 0;
}

/* ── Labels ── */
.cc-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

/* ── Inputs ── */
.cc-input {
    border: 1px solid var(--cc-border);
    border-radius: var(--cc-radius);
    font-size: 0.88rem;
    font-weight: 400;
    color: var(--cc-text);
    background: var(--cc-white);
    transition: border-color 0.15s;
    width: 100%;
    padding: 8px 10px;
    font-family: var(--cc-font);
}
.cc-input:focus {
    border-color: var(--cc-primary);
    outline: none;
    box-shadow: 0 0 0 2px rgba(15,82,186,0.12);
}
.cc-input::placeholder {
    color: #9ca3af;
    font-weight: 400;
}
.cc-textarea {
    resize: vertical;
    min-height: 80px;
    line-height: 1.6;
}

/* ── Buttons ── */
.cc-btn-primary {
    background: var(--cc-primary);
    border: 1px solid var(--cc-primary);
    color: var(--cc-white);
    font-family: var(--cc-font);
    font-weight: 600;
    font-size: 0.88rem;
    padding: 10px 20px;
    border-radius: var(--cc-radius);
    cursor: pointer;
    transition: background 0.12s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.cc-btn-primary:hover {
    background: var(--cc-primary-dark);
    border-color: var(--cc-primary-dark);
    color: var(--cc-white);
}
.cc-btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
.cc-btn-block {
    display: flex;
    width: 100%;
}

/* ── Saved Section ── */
.cc-saved-field {
    margin-bottom: 14px;
    padding-bottom: 14px;
    border-bottom: 1px solid #f1f5fb;
}
.cc-saved-field:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}
.cc-saved-text {
    font-size: 0.90rem;
    font-weight: 400;
    color: var(--cc-text);
    line-height: 1.6;
    white-space: pre-wrap;
    word-break: break-word;
    padding: 6px 10px;
    background: var(--cc-bg);
    border-radius: var(--cc-radius);
    border: 1px solid #f1f5fb;
    min-height: 36px;
}

/* ── Empty State ── */
.cc-empty-state {
    padding: 40px 20px;
    text-align: center;
}
.cc-empty-icon {
    font-size: 2.8rem;
    color: #d1d5db;
    margin-bottom: 12px;
}
.cc-empty-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #9ca3af;
    margin-bottom: 6px;
}
.cc-empty-text {
    font-size: 0.82rem;
    color: #b0b7c3;
    font-weight: 400;
    max-width: 280px;
    margin: 0 auto;
    line-height: 1.5;
}

/* ── Alerts ── */
.alert {
    border-radius: var(--cc-radius);
    font-size: 0.84rem;
    padding: 10px 14px;
    border: none;
    margin-bottom: 14px;
}
.cc-alert-success {
    background: var(--cc-green-bg);
    color: #065f46;
    border-left: 4px solid var(--cc-green);
}
.cc-alert-danger {
    background: var(--cc-red-bg);
    color: #991b1b;
    border-left: 4px solid var(--cc-red);
}

/* ── Select2 overrides ── */
.select2-container--default .select2-selection--single {
    border: 1px solid var(--cc-border) !important;
    border-radius: var(--cc-radius) !important;
    height: 38px !important;
    font-size: 0.88rem;
    font-family: var(--cc-font);
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important;
    padding-left: 10px;
    color: var(--cc-text);
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
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
    height: var(--cc-nav-height);
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
    font-size: 0.63rem;
    font-weight: 600;
    transition: color 0.15s;
    border-radius: var(--cc-radius);
}
.cc-nav-item i { font-size: 1.2rem; }
.cc-nav-item:hover { color: var(--cc-primary); text-decoration: none; }
.cc-nav-item:active,
.cc-nav-item.active { color: var(--cc-primary); }

/* ── Responsive ── */
@media (min-width: 768px) {
    .cc-page {
        padding: 24px 20px 16px;
    }
}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
if (typeof jQuery !== 'undefined') {
    jQuery(function($) {

        $('#templateid').select2({ width: '100%' });

        $('#templateid').on('change', function() {
            var tid = $(this).val();
            if (tid) {
                $('#formSection').show();
                $('#savedSection').show();
                loadDischargeData();
            } else {
                $('#formSection').hide();
                $('#savedSection').hide();
            }
        });

        $('#btnSaveDischarge').on('click', function() {
            var tid = $('#templateid').val();
            var treatment = $('#treatment_given').val().trim();
            var condition = $('#condition').val().trim();

            if (!tid) { showAlert('Please select a template!', 'error'); return; }
            if (!treatment) { showAlert('Please describe the treatment given!', 'error'); return; }
            if (!condition) { showAlert('Please enter condition at discharge!', 'error'); return; }

            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

            $.ajax({
                url: "{{ route('templates.discharge.ajax.add') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    templateid: tid,
                    treatment: treatment,
                    condition: condition,
                    follow_up: $('#follow_up').val().trim()
                },
                success: function(res) {
                    if (res.ok) {
                        showAlert('Discharge summary saved successfully.', 'success');
                        loadDischargeData();
                    } else {
                        showAlert('Error: ' + (res.message || 'Could not save'), 'error');
                    }
                },
                error: function() { showAlert('Server error!', 'error'); },
                complete: function() { btn.prop('disabled', false).html('<i class="fas fa-plus mr-1"></i> Save Discharge Note'); }
            });
        });

        function loadDischargeData() {
            var tid = $('#templateid').val();
            if (!tid) return;

            $.get("{{ route('templates.discharge.ajax.get') }}", { templateid: tid }, function(res) {
                var data = res.discharge;
                if (data && data.id) {
                    $('#savedTreatment').text(data.treatment || '—');
                    $('#savedCondition').text(data.condition || '—');
                    $('#savedFollowUp').text(data.follow_up || '—');
                    $('#emptyState').hide();
                    $('#savedContent').show();
                } else {
                    $('#emptyState').show();
                    $('#savedContent').hide();
                }
            });
        }

        function showAlert(message, type) {
            var cls = type === 'success' ? 'cc-alert-success' : 'cc-alert-danger';
            var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            var html =
                '<div class="alert ' + cls + ' alert-dismissible fade show" role="alert">' +
                    '<i class="fas ' + icon + ' mr-2"></i>' + message +
                    '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>' +
                '</div>';
            $('#alertMessage').html(html);
            setTimeout(function() { $('.alert').fadeOut('slow', function() { $(this).remove(); }); }, 3500);
        }

    });
}
</script>
@stop
