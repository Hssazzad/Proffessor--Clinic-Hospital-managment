@extends('adminlte::page')

@section('title', 'Round Patient Medicine')

@section('content_header')
@stop

@section('content')

{{-- ════════════════════════════════════════════════════════
     MEDICATION RECONCILIATION UI — Round Patient Medicine
════════════════════════════════════════════════════════ --}}
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
/* ── DESIGN TOKENS ── */
:root{
    --bg:#f0f4f8;
    --surf:#ffffff;
    --surf-hover:#f8fafc;
    --bdr:#e2e8f0;
    --bdr-strong:#cbd5e1;
    --txt:#1e293b;
    --txt-secondary:#64748b;
    --txt-muted:#94a3b8;
    --primary:#0f52ba;
    --primary-dk:#0a3d8f;
    --primary-lt:#e0ecfb;
    --primary-badge:#dbeafe;
    --red:#b91c1c;
    --red-bg:#fef2f2;
    --red-bdr:#fecaca;
    --red-badge:#dc2626;
    --green:#059669;
    --green-bg:#ecfdf5;
    --green-bdr:#a7f3d0;
    --amber:#d97706;
    --amber-bg:#fffbeb;
    --amber-bdr:#fde68a;
    --r:10px;
    --r-sm:6px;
    --r-pill:9999px;
    --shadow-sm:0 1px 2px rgba(0,0,0,.05);
    --shadow:0 1px 3px rgba(0,0,0,.1),0 1px 2px rgba(0,0,0,.06);
    --shadow-md:0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -2px rgba(0,0,0,.1);
    --font:'Inter',system-ui,-apple-system,sans-serif;
}
*,*::before,*::after{box-sizing:border-box;}
body,input,select,textarea,button{font-family:var(--font)!important;}

/* ── PAGE WRAPPER ── */
.mr-page{padding:0 0 60px;background:var(--bg);min-height:100vh;}

/* ── STICKY HEADER BAR ── */
.mr-header{
    position:sticky;top:0;z-index:100;
    background:var(--surf);
    border-bottom:1px solid var(--bdr);
    padding:12px 24px;
    display:flex;align-items:center;justify-content:space-between;
    gap:16px;
    box-shadow:var(--shadow-sm);
}
.mr-header-left{display:flex;align-items:center;gap:14px;}
.mr-back-btn{
    display:inline-flex;align-items:center;gap:6px;
    background:var(--surf);color:var(--txt-secondary);
    border:1px solid var(--bdr);border-radius:var(--r-sm);
    padding:7px 14px;font-size:13px;font-weight:600;
    text-decoration:none;cursor:pointer;
    transition:all .15s;
}
.mr-back-btn:hover{background:var(--surf-hover);color:var(--txt);border-color:var(--bdr-strong);text-decoration:none;}
.mr-page-title{font-size:16px;font-weight:800;color:var(--txt);letter-spacing:-.2px;}
.mr-patient-tag{
    font-size:13px;font-weight:500;color:var(--txt-secondary);
    margin-left:4px;
}
.mr-stage-badge{
    display:inline-flex;align-items:center;gap:6px;
    background:var(--primary-lt);color:var(--primary);
    border:1px solid #bfdbfe;
    border-radius:var(--r-pill);padding:5px 14px;
    font-size:12px;font-weight:700;letter-spacing:.3px;
    white-space:nowrap;
}
.mr-stage-badge .dot{
    width:7px;height:7px;border-radius:50%;
    background:var(--primary);
}

/* ── LAYOUT: SINGLE COLUMN COMPACT ── */
.mr-layout{
    max-width:1100px;
    margin:0 auto;
    padding:20px 24px;
}


/* ── CARD BASE ── */
.mr-card{
    background:var(--surf);
    border:1px solid var(--bdr);
    border-radius:var(--r);
    overflow:hidden;
    box-shadow:var(--shadow-sm);
}
.mr-card+.mr-card{margin-top:16px;}

/* ── ALLERGY CARD ── */
.mr-allergy-card{
    border:1.5px solid var(--red-bdr);
    border-radius:var(--r);
    background:var(--red-bg);
    overflow:hidden;
}
.mr-allergy-header{
    background:var(--red);
    padding:10px 14px;
    display:flex;align-items:center;justify-content:space-between;
    gap:8px;
}
.mr-allergy-header-left{
    display:flex;align-items:center;gap:8px;
    color:#fff;font-size:12px;font-weight:800;
    letter-spacing:.8px;text-transform:uppercase;
}
.mr-allergy-header-left i{font-size:14px;}
.mr-allergy-badge{
    background:rgba(255,255,255,.2);color:#fff;
    border:1px solid rgba(255,255,255,.3);
    border-radius:var(--r-sm);padding:2px 10px;
    font-size:11px;font-weight:800;
}
.mr-allergy-item{
    padding:12px 14px;
    border-bottom:1px solid var(--red-bdr);
    display:flex;align-items:center;justify-content:space-between;
}
.mr-allergy-item:last-child{border-bottom:none;}
.mr-allergy-name{font-size:15px;font-weight:700;color:var(--red);}
.mr-allergy-reaction{font-size:11px;color:#991b1b;font-weight:500;margin-top:2px;}
.mr-allergy-edit{
    background:transparent;border:none;
    color:#991b1b;font-size:14px;cursor:pointer;
    padding:4px;opacity:.6;transition:opacity .15s;
}
.mr-allergy-edit:hover{opacity:1;}
.mr-allergy-footer{
    padding:10px 14px;
    text-align:center;
}
.mr-allergy-report-btn{
    display:inline-flex;align-items:center;gap:6px;
    background:transparent;color:var(--red);
    border:1.5px dashed var(--red-bdr);border-radius:var(--r-sm);
    padding:6px 14px;font-size:12px;font-weight:700;
    cursor:pointer;transition:all .15s;width:100%;justify-content:center;
}
.mr-allergy-report-btn:hover{background:rgba(185,28,28,.05);border-color:var(--red);}

/* ── PATIENT INFO BAR (horizontal) ── */
.mr-patient-bar{
    background:var(--surf);
    border:1px solid var(--bdr);
    border-radius:var(--r);
    padding:14px 20px;
    display:flex;align-items:center;flex-wrap:wrap;
    gap:24px;
    box-shadow:var(--shadow-sm);
    margin-bottom:18px;
}
.mr-patient-bar-title{
    font-size:10px;font-weight:800;
    letter-spacing:1.2px;text-transform:uppercase;
    color:var(--txt-muted);
    margin-right:8px;
    display:flex;align-items:center;gap:6px;
}
.mr-patient-bar-title i{font-size:14px;color:var(--primary);}
.mr-pinfo{display:flex;flex-direction:column;gap:1px;}
.mr-pinfo-label{font-size:10px;font-weight:600;color:var(--txt-muted);letter-spacing:.5px;text-transform:uppercase;}
.mr-pinfo-value{font-size:13px;font-weight:700;color:var(--txt);}

/* ── MAIN CONTENT AREA ── */
.mr-main{}

/* ── FORM CARD (Add Medicine) ── */
.mr-form-card{
    background:var(--surf);
    border:1px solid var(--bdr);
    border-radius:var(--r);
    overflow:hidden;
    box-shadow:var(--shadow-sm);
    margin-bottom:20px;
}
.mr-form-header{
    padding:16px 20px;
    border-bottom:1px solid var(--bdr);
    display:flex;align-items:center;justify-content:space-between;
    gap:12px;flex-wrap:wrap;
}
.mr-form-header-left{display:flex;align-items:center;gap:10px;}
.mr-form-title{font-size:16px;font-weight:800;color:var(--txt);letter-spacing:-.2px;}
.mr-form-subtitle{font-size:13px;color:var(--txt-secondary);font-weight:500;}
.mr-form-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.mr-form-body{padding:20px;}

/* ── FORM ELEMENTS ── */
.mr-form-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:14px;
}
@media(max-width:1100px){
    .mr-form-grid{grid-template-columns:repeat(2,1fr);}
}
@media(max-width:600px){
    .mr-form-grid{grid-template-columns:1fr;}
}
.mr-form-group{}
.mr-form-group.span-2{grid-column:span 2;}
.mr-form-group.span-3{grid-column:span 3;}
.mr-form-group.span-4{grid-column:span 4;}
.mr-label{
    display:block;font-size:11px;font-weight:700;
    letter-spacing:.5px;text-transform:uppercase;
    color:var(--txt-secondary);margin-bottom:5px;
}
.mr-label .req{color:var(--red-badge);}
.mr-input,.mr-select{
    width:100%;border:1px solid var(--bdr);border-radius:var(--r-sm);
    padding:9px 12px;font-size:13px;font-weight:500;
    color:var(--txt);background:var(--surf);
    transition:border-color .15s,box-shadow .15s;
}
.mr-input:focus,.mr-select:focus{
    outline:none;border-color:var(--primary);
    box-shadow:0 0 0 3px rgba(15,82,186,.1);
}
.mr-select{
    appearance:none;-webkit-appearance:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat:no-repeat;
    background-position:right 12px center;
    padding-right:32px;
}

/* ── BUTTONS ── */
.mr-btn{
    display:inline-flex;align-items:center;gap:7px;
    border-radius:var(--r-sm);padding:9px 20px;
    font-size:13px;font-weight:700;cursor:pointer;
    border:none;transition:all .15s;letter-spacing:.2px;
    white-space:nowrap;
}
.mr-btn-primary{background:var(--primary);color:#fff;}
.mr-btn-primary:hover{background:var(--primary-dk);}
.mr-btn-outline{background:var(--surf);color:var(--primary);border:1.5px solid var(--primary);}
.mr-btn-outline:hover{background:var(--primary-lt);}
.mr-btn-success{background:var(--green);color:#fff;}
.mr-btn-success:hover{background:#047857;}
.mr-btn-danger{background:var(--red-badge);color:#fff;}
.mr-btn-danger:hover{background:var(--red);}
.mr-btn-ghost{background:transparent;color:var(--txt-secondary);border:1px solid var(--bdr);}
.mr-btn-ghost:hover{background:var(--surf-hover);border-color:var(--bdr-strong);}
.mr-btn-sm{padding:5px 12px;font-size:12px;}
.mr-btn-xs{padding:4px 8px;font-size:11px;}

/* ── MEDICATION TABLE ── */
.mr-med-card{
    background:var(--surf);
    border:1px solid var(--bdr);
    border-radius:var(--r);
    overflow:hidden;
    box-shadow:var(--shadow-sm);
}
.mr-med-header{
    padding:16px 20px;
    border-bottom:1px solid var(--bdr);
    display:flex;align-items:center;justify-content:space-between;
    flex-wrap:wrap;gap:12px;
}
.mr-med-header-left{display:flex;align-items:center;gap:10px;}
.mr-med-title{font-size:16px;font-weight:800;color:var(--txt);}
.mr-tab-pills{display:flex;gap:6px;}
.mr-tab-pill{
    padding:5px 14px;border-radius:var(--r-pill);
    font-size:12px;font-weight:700;cursor:pointer;
    border:1px solid var(--bdr);background:var(--surf);
    color:var(--txt-secondary);transition:all .15s;
}
.mr-tab-pill.active{
    background:var(--primary);color:#fff;border-color:var(--primary);
}
.mr-table-wrap{overflow-x:auto;}
.mr-table{
    width:100%;border-collapse:collapse;font-size:13px;
}
.mr-table thead tr{background:#f8fafc;}
.mr-table thead th{
    padding:10px 14px;
    font-size:10px;font-weight:800;
    letter-spacing:1px;text-transform:uppercase;
    color:var(--txt-muted);
    border-bottom:1.5px solid var(--bdr);
    text-align:left;white-space:nowrap;
}
.mr-table tbody tr{
    border-bottom:1px solid var(--bdr);
    transition:background .1s;
}
.mr-table tbody tr:hover{background:var(--surf-hover);}
.mr-table tbody tr:last-child{border-bottom:none;}
.mr-table tbody td{
    padding:12px 14px;vertical-align:middle;
    color:var(--txt);font-weight:500;
}
.mr-med-name{
    font-size:14px;font-weight:700;color:var(--primary);
    line-height:1.3;
}
.mr-med-dosage{
    font-size:11px;color:var(--txt-secondary);font-weight:500;
    margin-top:2px;
}
.mr-freq-text{font-weight:600;color:var(--txt);}
.mr-last-taken{font-size:12px;color:var(--txt-secondary);font-weight:500;}

/* Reconciliation action buttons */
.mr-recon-actions{display:flex;align-items:center;gap:4px;}
.mr-action-btn{
    width:28px;height:28px;border-radius:var(--r-sm);
    display:inline-flex;align-items:center;justify-content:center;
    font-size:12px;cursor:pointer;border:none;
    transition:all .15s;
}
.mr-action-check{background:var(--green-bg);color:var(--green);border:1px solid var(--green-bdr);}
.mr-action-check:hover{background:var(--green);color:#fff;}
.mr-action-edit{background:var(--amber-bg);color:var(--amber);border:1px solid var(--amber-bdr);}
.mr-action-edit:hover{background:var(--amber);color:#fff;}
.mr-action-delete{background:var(--red-bg);color:var(--red-badge);border:1px solid var(--red-bdr);}
.mr-action-delete:hover{background:var(--red-badge);color:#fff;}
.mr-action-resume{
    background:var(--primary);color:#fff;
    border:none;border-radius:var(--r-sm);
    padding:5px 12px;font-size:11px;font-weight:700;
    display:inline-flex;align-items:center;gap:5px;
    cursor:pointer;transition:all .15s;
}
.mr-action-resume:hover{background:var(--primary-dk);}

/* ── RECONCILIATION FOOTER ── */
.mr-recon-footer{
    padding:14px 20px;
    border-top:1px solid var(--bdr);
    display:flex;align-items:center;justify-content:flex-end;
    gap:14px;
    background:#f8fafc;
}
.mr-recon-count{
    font-size:13px;font-weight:600;color:var(--green);
    font-style:italic;
}

/* ── BOTTOM STATUS BAR ── */
.mr-status-bar{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:16px;
    padding:0 24px;
    margin-top:20px;
}
@media(max-width:768px){
    .mr-status-bar{grid-template-columns:1fr;}
}
.mr-status-card{
    background:var(--surf);
    border:1px solid var(--bdr);
    border-radius:var(--r);
    padding:14px 18px;
    display:flex;align-items:center;gap:14px;
    box-shadow:var(--shadow-sm);
    transition:box-shadow .15s;
}
.mr-status-card:hover{box-shadow:var(--shadow);}
.mr-status-icon{
    width:42px;height:42px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-size:16px;flex-shrink:0;
}
.mr-status-icon.rx{background:var(--primary-lt);color:var(--primary);border:1.5px solid #bfdbfe;}
.mr-status-icon.interact{background:var(--green-bg);color:var(--green);border:1.5px solid var(--green-bdr);}
.mr-status-icon.pharm{background:#f1f5f9;color:var(--txt-secondary);border:1.5px solid var(--bdr);}
.mr-status-label{font-size:10px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--txt-muted);}
.mr-status-value{font-size:14px;font-weight:700;color:var(--txt);margin-top:2px;}

/* ── ALERT MESSAGE ── */
#ajaxMessage .alert{
    border-radius:var(--r-sm);font-size:13px;
    font-weight:600;padding:10px 16px;margin-bottom:0;
    border:none;
}
#ajaxMessage .alert-success{background:var(--green-bg);color:#065f46;border:1px solid var(--green-bdr);}
#ajaxMessage .alert-danger{background:var(--red-bg);color:var(--red);border:1px solid var(--red-bdr);}

/* ── MODAL OVERRIDES ── */
.modal-content{border-radius:var(--r);border:1px solid var(--bdr);overflow:hidden;}
.modal-header.bg-primary{background:var(--primary)!important;}
.modal-header{border-radius:0;}
.form-control{border-radius:var(--r-sm)!important;border-color:var(--bdr)!important;font-family:var(--font)!important;}
.form-control:focus{border-color:var(--primary)!important;box-shadow:0 0 0 3px rgba(15,82,186,.1)!important;}

/* ── EMPTY STATE ── */
.mr-empty{
    padding:48px 20px;text-align:center;color:var(--txt-muted);
}
.mr-empty i{font-size:36px;opacity:.3;margin-bottom:12px;display:block;}
.mr-empty p{font-size:13px;font-weight:500;}

/* ── PRINT ── */
@media print{.no-print{display:none!important;}}
</style>

{{-- hidden inputs (logic unchanged) --}}
<div id="ajaxMessage" class="px-3 pt-3 no-print"></div>
<input type="hidden" id="patientcode"        value="{{ $patientcode }}">
<input type="hidden" id="print_patientname"  value="{{ $patient->patientname ?? '' }}">
<input type="hidden" id="print_mobile"       value="{{ $patient->mobile_no ?? '' }}">
<input type="hidden" id="print_admissiondate" value="{{ $admission ? date('d-m-Y', strtotime($admission->created_at)) : date('d-m-Y') }}">

<div class="mr-page">

    {{-- ══ STICKY HEADER ══ --}}
    <div class="mr-header no-print">
        <div class="mr-header-left">
            <a href="{{ url()->previous() }}" class="mr-back-btn">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <span class="mr-page-title">
                Round Patient Medicine
                <span class="mr-patient-tag">— {{ $patient->patientname ?? '' }} ({{ $patientcode }})</span>
            </span>
        </div>
        @if(isset($admission) && $admission)
            @php
                $stageNames=[1=>'Admit Medicine',2=>'Pre Surgery',3=>'Post Surgery',4=>'Round Patient',5=>'Fresh Prescription',6=>'Release Patient'];
                $s=(int)($admission->status??4);
            @endphp
            <span class="mr-stage-badge"><span class="dot"></span> Stage {{ $s }}: {{ $stageNames[$s] ?? 'Round Patient' }}</span>
        @endif
    </div>

    {{-- ══ COMPACT SINGLE-COLUMN LAYOUT ══ --}}
    <div class="mr-layout">

        {{-- ── Patient Info Bar (horizontal) ── --}}
        <div class="mr-patient-bar no-print">
            <span class="mr-patient-bar-title"><i class="fas fa-user-injured"></i> Patient</span>
            <div class="mr-pinfo">
                <span class="mr-pinfo-label">Name</span>
                <span class="mr-pinfo-value">{{ $patient->patientname ?? 'N/A' }}</span>
            </div>
            <div class="mr-pinfo">
                <span class="mr-pinfo-label">Code</span>
                <span class="mr-pinfo-value">{{ $patientcode }}</span>
            </div>
            <div class="mr-pinfo">
                <span class="mr-pinfo-label">Mobile</span>
                <span class="mr-pinfo-value">{{ $patient->mobile_no ?? 'N/A' }}</span>
            </div>
            <div class="mr-pinfo">
                <span class="mr-pinfo-label">Admission Date</span>
                <span class="mr-pinfo-value">{{ $admission ? date('d M Y', strtotime($admission->created_at)) : date('d M Y') }}</span>
            </div>
        </div>

        {{-- History Card --}}
        @if(isset($roundHistory) && $roundHistory->count() > 0)
        <div class="mr-card no-print" style="margin-bottom: 20px;">
            <div class="mr-form-header" style="background:#e0f2fe; border-bottom-color:#bae6fd; padding:12px 20px;">
                <div class="mr-form-header-left">
                    <span class="mr-form-title" style="color:#0369a1; font-size:14px;"><i class="fas fa-history"></i> Round History ({{ $roundHistory->count() }} round{{ $roundHistory->count() > 1 ? 's' : '' }})</span>
                </div>
            </div>
            <div class="mr-form-body" style="padding:0;">
                <table class="mr-table">
                    <thead style="background:#f0f9ff; color:#0c4a6e;">
                        <tr>
                            <th style="padding:8px 16px;">#</th>
                            <th>Date</th>
                            <th>Medicines Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roundHistory as $i => $round)
                        <tr>
                            <td style="padding:8px 16px;">{{ $i + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($round->round_date)->format('d-m-Y') }}</td>
                            <td><span class="mr-stage-badge" style="padding:2px 8px;">{{ $round->medicine_count }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- ── MAIN CONTENT ── --}}

            {{-- ▸ Add Medicine Form ▸ --}}
            <div class="mr-form-card no-print">
                <div class="mr-form-header">
                    <div class="mr-form-header-left">
                        <i class="fas fa-pills" style="color:var(--primary);font-size:18px;"></i>
                        <div>
                            <div class="mr-form-title">Add New Medicine</div>
                            <div class="mr-form-subtitle">Fill in the details to add a medicine to this admission</div>
                        </div>
                    </div>
                    <div class="mr-form-actions">
                        <button type="button" class="mr-btn mr-btn-primary" onclick="savePrintOnly()">
                            <i class="fas fa-print"></i> Save & Print
                        </button>
                        <select id="nextStageSelect" class="mr-input mr-select" style="width:auto;min-width:175px;padding-top:8px;padding-bottom:8px;font-size:12px;" title="Next stage for this patient">
                            <option value="5">Go to: Fresh Prescription</option>
                            <option value="2">Go to: Pre Surgery</option>
                            <option value="3">Go to: Post Surgery</option>
                            <option value="6">Go to: Discharge</option>
                        </select>
                        <button type="button" class="mr-btn mr-btn-success btn-save-print-next" onclick="savePrintNext()">
                            <i class="fas fa-print"></i> Save & Print & Go to Fresh Prescription
                        </button>
                    </div>
                </div>
                <div class="mr-form-body">
                    <form id="medicineFormInline">
                        @csrf
                        <input type="hidden" name="id"           id="inf_id">
                        <input type="hidden" name="patientcode"  id="inf_patientcode" value="{{ $patientcode }}">
                        <input type="hidden" name="order_type"   id="inf_order_type"  value="round">
                        <input type="hidden" name="templeteid"   id="inf_templeteid"  value="TPL-000001">

                        <div class="mr-form-grid">
                            <div class="mr-form-group span-2">
                                <label class="mr-label">Medicine Name <span class="req">*</span></label>
                                <select name="name" id="inf_name" class="mr-input mr-select" required>
                                    <option value="">-- Select Medicine --</option>
                                    @foreach($medicine as $med)
                                        <option value="{{ $med->name }}">{{ $med->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mr-form-group">
                                <label class="mr-label">Strength</label>
                                <input type="text" name="strength" id="inf_strength" class="mr-input" placeholder="e.g. 500mg">
                            </div>

                            <div class="mr-form-group">
                                <label class="mr-label">Dose Schedule (M · N · N)</label>
                                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;">
                                    <div style="text-align:center;">
                                        <label class="mr-label" style="text-align:center;font-size:9px;">AM</label>
                                        <input type="text" name="morning" id="inf_morning" class="mr-input" placeholder="1" style="text-align:center;font-size:15px;font-weight:800;color:var(--primary);padding:8px 6px;">
                                    </div>
                                    <div style="text-align:center;">
                                        <label class="mr-label" style="text-align:center;font-size:9px;">Noon</label>
                                        <input type="text" name="noon" id="inf_noon" class="mr-input" placeholder="0" style="text-align:center;font-size:15px;font-weight:800;color:var(--primary);padding:8px 6px;">
                                    </div>
                                    <div style="text-align:center;">
                                        <label class="mr-label" style="text-align:center;font-size:9px;">PM</label>
                                        <input type="text" name="night" id="inf_night" class="mr-input" placeholder="1" style="text-align:center;font-size:15px;font-weight:800;color:var(--primary);padding:8px 6px;">
                                    </div>
                                </div>
                            </div>
                            <div class="mr-form-group">
                                <label class="mr-label">Meal Timing</label>
                                <select name="timing" id="inf_timing" class="mr-input mr-select">
                                    <option value="">-- Select --</option>
                                    <option value="before">Before Meal</option>
                                    <option value="after">After Meal</option>
                                    <option value="with">With Meal</option>
                                    <option value="empty">Empty Stomach</option>
                                </select>
                            </div>
                            <div class="mr-form-group">
                                <label class="mr-label">Duration</label>
                                <input type="text" name="duration" id="inf_duration" class="mr-input" placeholder="e.g. 7 Days">
                            </div>
                            <div class="mr-form-group">
                                <label class="mr-label">Special Instructions</label>
                                <input type="text" name="instruction" id="inf_instruction" class="mr-input" placeholder="Any special notes...">
                            </div>
                        </div>

                        <div style="display:flex;gap:8px;margin-top:16px;justify-content:flex-end;">
                            <button type="reset" class="mr-btn mr-btn-ghost mr-btn-sm">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                            <button type="submit" class="mr-btn mr-btn-primary">
                                <i class="fas fa-plus-circle"></i> Add Medicine
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ▸ Medicine List Table ▸ --}}
            <div class="mr-med-card">
                <div class="mr-med-header">
                    <div class="mr-med-header-left">
                        <span class="mr-med-title">Active Admission Medications</span>
                    </div>
                    <div class="mr-tab-pills">
                        <span class="mr-tab-pill active">All ({{ isset($medicines) ? $medicines->count() : 0 }})</span>
                    </div>
                </div>
                <div id="medicineTableArea">
                    @include('admission.roundpatient.roundmedicine_rows')
                </div>
            </div>

    </div>{{-- /mr-layout --}}

    {{-- ══ BOTTOM STATUS BAR ══ --}}
    <div class="mr-status-bar no-print">
        <div class="mr-status-card">
            <div class="mr-status-icon rx">
                <i class="fas fa-file-prescription"></i>
            </div>
            <div>
                <div class="mr-status-label">New RX Request</div>
                <div class="mr-status-value">Awaiting Signature</div>
            </div>
        </div>
        <div class="mr-status-card">
            <div class="mr-status-icon interact">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <div class="mr-status-label">Interactions</div>
                <div class="mr-status-value">0 Contradictions Found</div>
            </div>
        </div>
        <div class="mr-status-card">
            <div class="mr-status-icon pharm">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div>
                <div class="mr-status-label">Pharmacy Status</div>
                <div class="mr-status-value">Verify Order Set</div>
            </div>
        </div>
    </div>

</div>{{-- /mr-page --}}

{{-- ══════════════════════════════════════════════════
     MODAL (kept for editMedicineBtn compatibility)
══════════════════════════════════════════════════ --}}
<div class="modal fade" id="medicineModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="medicineForm">
            @csrf
            <input type="hidden" name="id"          id="medicine_id">
            <input type="hidden" name="key"         id="key">
            <input type="hidden" name="patientcode" id="modal_patientcode" value="{{ $patientcode }}">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Edit Medicine</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label><b>Medicine Name <span class="text-danger">*</span></b></label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Strength</label>
                            <input type="text" name="strength" id="strength" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Morning</label>
                            <input type="text" name="morning" id="morning" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Noon</label>
                            <input type="text" name="noon" id="noon" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Night</label>
                            <input type="text" name="night" id="night" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Meal Timing</label>
                            <select name="timing" id="timing" class="form-control">
                                <option value="">-- Select --</option>
                                <option value="before">Before Meal</option>
                                <option value="after">After Meal</option>
                                <option value="with">With Meal</option>
                                <option value="empty">Empty Stomach</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Duration</label>
                            <input type="text" name="duration" id="duration" class="form-control" placeholder="e.g. 7 Days">
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Instruction</label>
                            <input type="text" name="instruction" id="instruction" class="form-control">
                        </div>
                        <input type="hidden" name="order_type" id="order_type" value="round">
                        <div class="col-md-6 form-group">
                            <label>Template ID</label>
                            <input type="text" name="templeteid" id="templeteid" class="form-control" value="TPL-000001">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('js')
@include('admission.partials.print_prescription_helpers')
<script>
var logoUrl = @json(config('app.logo_url'));

function escapeHtml(value) {
    return $('<div>').text(value || '').html();
}

function showAjaxMessage(type, message) {
    $('#ajaxMessage').html('<div class="alert alert-' + type + '">' + escapeHtml(message) + '</div>');
}

function ajaxErrorMessage(xhr, fallback) {
    if (xhr.responseJSON && xhr.responseJSON.message) {
        return xhr.responseJSON.message;
    }
    return fallback;
}

/* ── print via server-side route (partials/rx_page) ── */
function printSlip(){
    var patientcode = $('#patientcode').val();
    var stageTitle = 'Round Patient';
    var printUrl = "{{ route('admission.roundmedicine.print') }}?patientcode=" + encodeURIComponent(patientcode) + "&stageTitle=" + encodeURIComponent(stageTitle);
    window.open(printUrl,'_blank');
}

function savePrintOnly(){
    var patientcode = $('#patientcode').val();
    $.ajax({
        url:"{{ route('admission.roundmedicineSaveAll.save') }}",
        type:"POST",
        data:{_token:"{{ csrf_token() }}",patientcode:patientcode},
        success:function(response){
            if(response.success){
                // 1. Open print first (no next stage - patient stays in Round)
                var stageTitle = 'Round Patient';
                var printUrl = "{{ route('admission.roundmedicine.print') }}?patientcode=" + encodeURIComponent(patientcode) + "&stageTitle=" + encodeURIComponent(stageTitle);
                window.open(printUrl,'_blank');
                showAjaxMessage('success', response.message || 'Saved & Printed. Patient stays in Round.');
                setTimeout(function(){
                    window.location.href = "{{ route('admission.roundmedicine') }}";
                }, 1500);
            }else{
                showAjaxMessage('danger', response.message || 'Save failed.');
            }
        },
        error:function(xhr){
            showAjaxMessage('danger', ajaxErrorMessage(xhr, 'Save failed.'));
        }
    });
}

var STAGE_LABELS = {2:'Pre Surgery', 3:'Post Surgery', 4:'Round Patient', 5:'Fresh Prescription', 6:'Discharge'};

$(document).on('change','#nextStageSelect',function(){
    var label = STAGE_LABELS[$(this).val()] || '';
    $('.btn-save-print-next').html('<i class="fas fa-print"></i> Save & Print & Go to ' + label);
});

function savePrintNext(){
    var patientcode = $('#patientcode').val();
    var targetStatus = $('#nextStageSelect').val();
    $.ajax({
        url:"{{ route('admission.roundmedicineSaveAll.save') }}",
        type:"POST",
        data:{_token:"{{ csrf_token() }}",patientcode:patientcode},
        success:function(response){
            if(response.success){
                // 1. Open print first
                var stageTitle = 'Round Patient';
                var printUrl = "{{ route('admission.roundmedicine.print') }}?patientcode=" + encodeURIComponent(patientcode) + "&stageTitle=" + encodeURIComponent(stageTitle);
                window.open(printUrl,'_blank');

                // 2. Next Stage after short delay (let print request start first)
                setTimeout(function(){
                    $.ajax({
                        url:"{{ route('admission.nextStage') }}",type:"POST",
                        data:{_token:"{{ csrf_token() }}",patientcode:patientcode,target_status:targetStatus},
                        success:function(res){
                            if(res.success){
                                showAjaxMessage('success', 'Saved & Printed. '+res.message);
                                setTimeout(function(){
                                    window.location.href = res.redirect_url || "{{ route('admission.roundmedicine') }}";
                                }, 1500);
                            }else{
                                showAjaxMessage('danger', res.message || 'Stage update failed.');
                            }
                        }
                    });
                }, 800);
            }else{
                showAjaxMessage('danger', response.message || 'Save failed.');
            }
        },
        error:function(xhr){
            showAjaxMessage('danger', ajaxErrorMessage(xhr, 'Save failed.'));
        }
    });
}
</script>
@endsection

@push('js')
<script>
function loadMedicineTable(){
    $.ajax({
        url:"{{ route('admission.roundmedicineList.list') }}",
        type:"POST",
        data:{_token:"{{ csrf_token() }}",patientcode:$('#patientcode').val()},
        success:function(response){$('#medicineTableArea').html(response);}
    });
}

$(document).ready(function(){

    /* ── Inline form: Add New Medicine ── */
    $('#medicineFormInline').on('submit',function(e){
        e.preventDefault();
        var data={
            _token:"{{ csrf_token() }}",
            id:$('#inf_id').val(),
            patientcode:$('#patientcode').val(),
            name:$('#inf_name').val(),
            strength:$('#inf_strength').val(),
            morning:$('#inf_morning').val(),
            noon:$('#inf_noon').val(),
            night:$('#inf_night').val(),
            timing:$('#inf_timing').val(),
            duration:$('#inf_duration').val(),
            instruction:$('#inf_instruction').val(),
            order_type:'round',
            templeteid:$('#inf_templeteid').val()
        };
        $.ajax({
            url:"{{ route('admission.roundmedicineSave.save') }}",
            type:"POST",data:data,
            success:function(response){
                if (!response.success) {
                    showAjaxMessage('danger', response.message || 'Save failed.');
                    return;
                }
                showAjaxMessage('success', response.message);
                $('#medicineFormInline')[0].reset();
                $('#inf_id').val('');
                loadMedicineTable();
            },
            error:function(xhr){
                showAjaxMessage('danger', ajaxErrorMessage(xhr, 'Something went wrong.'));
            }
        });
    });

    /* ── addMedicineBtn → scroll to / focus inline form ── */
    $('#addMedicineBtn').on('click',function(){
        $('#inf_name').focus();
        $('html,body').animate({scrollTop:$('#inf_name').offset().top-80},300);
    });

    /* ── Modal form: Edit ── */
    $('#medicineForm').on('submit',function(e){
        e.preventDefault();
        $.ajax({
            url:"{{ route('admission.roundmedicineSave.save') }}",
            type:"POST",data:$(this).serialize(),
            success:function(response){
                if (!response.success) {
                    showAjaxMessage('danger', response.message || 'Medicine save failed.');
                    return;
                }
                $('#medicineModal').modal('hide');
                showAjaxMessage('success', response.message);
                loadMedicineTable();
            },
            error:function(xhr){
                showAjaxMessage('danger', ajaxErrorMessage(xhr, 'Something went wrong.'));
            }
        });
    });

    /* ── Edit button (in table rows) ── */
    $(document).on('click','.editMedicineBtn',function(){
        $('#medicine_id').val($(this).data('id'));
        $('#key').val($(this).data('key'));
        $('#name').val($(this).data('name'));
        $('#strength').val($(this).data('strength'));
        $('#morning').val($(this).data('morning'));
        $('#noon').val($(this).data('noon'));
        $('#night').val($(this).data('night'));
        $('#timing').val($(this).data('timing'));
        $('#duration').val($(this).data('duration'));
        $('#instruction').val($(this).data('instruction'));
        $('#templeteid').val($(this).data('templeteid'));
        $('#order_type').val($(this).data('order_type') || 'round');
        $('#modalTitle').text('Edit Medicine');
        $('#medicineModal').modal('show');
    });

    /* ── Delete button ── */
    $(document).on('click','.deleteMedicineBtn',function(){
        if(!confirm('Are you sure you want to delete this medicine?'))return;
        var key=$(this).data('key');
        $.ajax({
            url:"{{ route('admission.roundmedicine.delete') }}",
            type:"POST",
            data:{_token:"{{ csrf_token() }}",patientcode:$('#patientcode').val(),key:key},
            success:function(response){
                if (!response.success) {
                    showAjaxMessage('danger', response.message || 'Delete failed.');
                    return;
                }
                showAjaxMessage('success', response.message);
                loadMedicineTable();
            },
            error:function(xhr){
                showAjaxMessage('danger', ajaxErrorMessage(xhr, 'Delete failed.'));
            }
        });
    });

});
</script>
@endpush

@push('js')
@if(isset($activeMenu))
<script>
$(document).ready(function(){
    let menu=$('a[href*="{{ $activeMenu }}"]');
    menu.addClass('active');
    menu.closest('.nav-item').addClass('menu-open');
    menu.closest('.nav-treeview').css('display','block');
});
</script>
@endif
@endpush