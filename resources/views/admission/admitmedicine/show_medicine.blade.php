@extends('adminlte::page')

@section('title', 'Patient Medicine')

@section('content_header')
@stop

@section('content')

{{-- ════════════════════════════════════════════════════════
     CLINICAL CLARITY V2  —  Admit Medicine
════════════════════════════════════════════════════════ --}}
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
/* ── TOKENS ── */
:root{
    --p:#0f52ba;--p-dk:#0a3d8f;--p-lt:#e8effc;
    --surf:#ffffff;--dim:#f3f3fc;
    --out:#e5e7eb;--bdr:#d0d8f0;
    --txt:#111827;--muted:#6b7280;
    --red:#dc2626;--red-lt:#fef2f2;--red-bdr:#fecaca;
    --grn:#059669;--grn-lt:#ecfdf5;--grn-bdr:#6ee7b7;
    --r:4px;
    --font:'Hanken Grotesk',Arial,sans-serif;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body,input,select,textarea,button{font-family:var(--font)!important;}

/* ── PAGE SHELL ── */
.cc-page{padding:0 0 48px;}

/* ── STICKY HEADER ── */
.cc-header{
    position:sticky;top:0;z-index:100;
    background:var(--p);
    display:flex;align-items:center;justify-content:space-between;
    padding:13px 20px;
    box-shadow:0 2px 8px rgba(15,82,186,.25);
}
.cc-header-left{display:flex;align-items:center;gap:12px;}
.cc-back-btn{
    background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.3);
    color:#fff;border-radius:var(--r);padding:6px 12px;
    font-size:12px;font-weight:700;cursor:pointer;
    display:flex;align-items:center;gap:6px;text-decoration:none;
    transition:background .15s;
}
.cc-back-btn:hover{background:rgba(255,255,255,.28);color:#fff;text-decoration:none;}
.cc-page-title{
    color:#fff;font-size:15px;font-weight:800;
    letter-spacing:.3px;
}
.cc-stage-badge{
    background:rgba(255,255,255,.22);color:#fff;
    border:1px solid rgba(255,255,255,.35);
    border-radius:99px;padding:4px 12px;
    font-size:11px;font-weight:700;letter-spacing:.5px;
    white-space:nowrap;
}

/* ── ACTION TOOLBAR ── */
.cc-toolbar{
    background:var(--surf);
    border-bottom:1px solid var(--out);
    padding:12px 20px;
    display:flex;align-items:center;gap:10px;flex-wrap:wrap;
}
.cc-btn{
    display:inline-flex;align-items:center;gap:7px;
    border-radius:var(--r);padding:8px 18px;
    font-size:13px;font-weight:700;cursor:pointer;
    border:none;transition:all .15s;letter-spacing:.2px;
    white-space:nowrap;
}
.cc-btn-outline{
    background:var(--surf);color:var(--p);
    border:1.5px solid var(--p);
}
.cc-btn-outline:hover{background:var(--p-lt);}
.cc-btn-primary{background:var(--p);color:#fff;}
.cc-btn-primary:hover{background:var(--p-dk);}
.cc-btn-danger{background:var(--red);color:#fff;}
.cc-btn-sm{padding:5px 12px;font-size:12px;}
.cc-btn-icon{padding:5px 9px;font-size:12px;}

/* ── LAYOUT ── */
.cc-layout{
    padding:20px;
    display:grid;
    grid-template-columns:360px 1fr;
    gap:20px;
    align-items:start;
}
@media(max-width:900px){
    .cc-layout{grid-template-columns:1fr;}
}

/* ── CARD ── */
.cc-card{
    background:var(--surf);
    border:1px solid var(--bdr);
    border-radius:var(--r);
    overflow:hidden;
}
.cc-card-head{
    background:var(--dim);
    border-bottom:1px solid var(--bdr);
    padding:12px 16px;
    display:flex;align-items:center;justify-content:space-between;
    gap:8px;
}
.cc-card-head-title{
    font-size:11px;font-weight:800;
    letter-spacing:1.5px;text-transform:uppercase;
    color:var(--p);
}
.cc-card-body{padding:16px;}

/* ── FORM ── */
.cc-form-group{margin-bottom:13px;}
.cc-label{
    display:block;font-size:10px;font-weight:800;
    letter-spacing:1px;text-transform:uppercase;
    color:var(--muted);margin-bottom:5px;
}
.cc-label .req{color:var(--red);}
.cc-input,.cc-select{
    width:100%;border:1px solid var(--bdr);border-radius:var(--r);
    padding:8px 11px;font-size:13px;font-weight:600;
    color:var(--txt);background:var(--surf);
    transition:border-color .15s,box-shadow .15s;
    appearance:none;-webkit-appearance:none;
}
.cc-input:focus,.cc-select:focus{
    outline:none;border-color:var(--p);
    box-shadow:0 0 0 3px rgba(15,82,186,.12);
}

/* Dose grid M-N-N */
.dose-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;}
.dose-cell{text-align:center;}
.dose-cell .cc-label{text-align:center;}
.dose-cell .cc-input{text-align:center;font-size:14px;font-weight:800;color:var(--p);}

/* ── MEDICINE TABLE ── */
.cc-table-wrap{overflow-x:auto;}
.cc-table{
    width:100%;border-collapse:collapse;
    font-size:12.5px;
}
.cc-table thead tr{
    background:var(--dim);
}
.cc-table thead th{
    padding:9px 10px;
    font-size:9.5px;font-weight:800;
    letter-spacing:1.2px;text-transform:uppercase;
    color:var(--muted);border-bottom:1.5px solid var(--bdr);
    white-space:nowrap;text-align:left;
}
.cc-table tbody tr{
    border-bottom:1px solid var(--out);
    transition:background .1s;
}
.cc-table tbody tr:hover{background:var(--dim);}
.cc-table tbody td{
    padding:9px 10px;vertical-align:middle;
    color:var(--txt);font-weight:600;
}

/* Dose badge */
.dose-badge{
    display:inline-flex;align-items:center;gap:3px;
}
.db{
    min-width:22px;height:22px;
    border-radius:var(--r);
    display:inline-flex;align-items:center;justify-content:center;
    font-size:11px;font-weight:800;
    background:var(--p-lt);color:var(--p);
}
.db.zero{background:var(--dim);color:var(--muted);}

/* ── SAFETY CARD ── */
.cc-allergy-card{
    border:1.5px solid var(--red-bdr);
    border-radius:var(--r);
    background:var(--red-lt);
    padding:12px 14px;
    margin-top:12px;
}
.cc-allergy-head{
    display:flex;align-items:center;gap:8px;
    font-size:11px;font-weight:800;
    letter-spacing:1px;text-transform:uppercase;
    color:var(--red);margin-bottom:6px;
}
.cc-allergy-body{font-size:12px;color:#7f1d1d;font-weight:500;line-height:1.6;}

/* ── PHARMACY STATUS ── */
.cc-pharmacy-card{
    border:1.5px solid var(--grn-bdr);
    border-radius:var(--r);
    background:var(--grn-lt);
    padding:10px 14px;
    margin-top:10px;
    display:flex;align-items:center;gap:10px;
}
.cc-pharmacy-dot{
    width:10px;height:10px;border-radius:50%;
    background:var(--grn);flex-shrink:0;
}
.cc-pharmacy-text{font-size:12px;font-weight:700;color:#065f46;}

/* ── EMPTY STATE ── */
.cc-empty{
    padding:36px 16px;text-align:center;
    color:var(--muted);font-size:13px;
}
.cc-empty i{font-size:32px;opacity:.3;margin-bottom:10px;display:block;}

/* ── ALERT ── */
#ajaxMessage .alert{
    border-radius:var(--r);font-size:13px;
    font-weight:600;padding:10px 16px;margin-bottom:0;
}

/* ── MODAL OVERRIDES ── */
.modal-content{border-radius:var(--r);border:1px solid var(--bdr);}
.modal-header.bg-primary{background:var(--p)!important;}
.modal-header{border-radius:var(--r) var(--r) 0 0;}
.form-control{border-radius:var(--r)!important;border-color:var(--bdr)!important;font-family:var(--font)!important;}
.form-control:focus{border-color:var(--p)!important;box-shadow:0 0 0 3px rgba(15,82,186,.12)!important;}

/* ── PRINT ── */
@media print{.no-print{display:none!important;}}
</style>

{{-- hidden inputs (logic unchanged) --}}
<div id="ajaxMessage" class="px-3 pt-3 no-print"></div>
<input type="hidden" id="patientcode"        value="{{ $patientcode }}">
<input type="hidden" id="print_patientname"  value="{{ $patient->patientname ?? '' }}">
<input type="hidden" id="print_mobile"       value="{{ $patient->mobile_no ?? '' }}">
<input type="hidden" id="print_admissiondate" value="{{ $admission ? date('d-m-Y', strtotime($admission->created_at)) : date('d-m-Y') }}">

<div class="cc-page">

    {{-- ══ STICKY HEADER ══ --}}
    <div class="cc-header no-print">
        <div class="cc-header-left">
            <a href="{{ url()->previous() }}" class="cc-back-btn">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <span class="cc-page-title">
                Medicine List
                <span style="opacity:.6;font-weight:500;font-size:13px;margin-left:6px;">
                    — {{ $patientcode }}
                </span>
            </span>
        </div>
        @if(isset($admission) && $admission)
            @php
                $stageNames=[1=>'Admit Medicine',2=>'Pre Surgery',3=>'Post Surgery',4=>'Round Patient',5=>'Fresh Prescription',6=>'Release Patient'];
                $s=(int)($admission->status??1);
            @endphp
            <span class="cc-stage-badge">Stage {{ $s }}: {{ $stageNames[$s] ?? '' }}</span>
        @endif
    </div>

    {{-- ══ ACTION TOOLBAR ══ --}}
    <div class="cc-toolbar no-print">
        <button type="button" class="cc-btn cc-btn-outline" id="addMedicineBtn">
            <i class="fas fa-plus-circle"></i> Add Admit Medicine
        </button>
        <button type="button" class="cc-btn cc-btn-primary btn-save-print-next">
            <i class="fas fa-save"></i> Save &amp; Print &amp; Next
        </button>
    </div>

    {{-- ══ TWO-COLUMN LAYOUT ══ --}}
    <div class="cc-layout">

        {{-- ── LEFT: Add Form Card ── --}}
        <div class="cc-card no-print">
            <div class="cc-card-head">
                <span class="cc-card-head-title"><i class="fas fa-pills" style="opacity:.7;margin-right:4px;"></i> Add Medicine</span>
            </div>
            <div class="cc-card-body">
                <form id="medicineFormInline">
                    @csrf
                    <input type="hidden" name="id"           id="inf_id">
                    <input type="hidden" name="patientcode"  id="inf_patientcode" value="{{ $patientcode }}">
                    <input type="hidden" name="order_type"   id="inf_order_type"  value="admit">
                    <input type="hidden" name="templeteid"   id="inf_templeteid"  value="TPL-000001">

                    <div class="cc-form-group">
                        <label class="cc-label">Medicine Name <span class="req">*</span></label>
                        <input type="text" name="name" id="inf_name" class="cc-input" required placeholder="e.g. Paracetamol">
                    </div>
                    <div class="cc-form-group">
                        <label class="cc-label">Strength</label>
                        <input type="text" name="strength" id="inf_strength" class="cc-input" placeholder="e.g. 500mg">
                    </div>
                    <div class="cc-form-group">
                        <label class="cc-label">Dose (Morning · Noon · Night)</label>
                        <div class="dose-grid">
                            <div class="dose-cell">
                                <label class="cc-label" style="font-size:9px;">Morning</label>
                                <input type="text" name="morning" id="inf_morning" class="cc-input" placeholder="1">
                            </div>
                            <div class="dose-cell">
                                <label class="cc-label" style="font-size:9px;">Noon</label>
                                <input type="text" name="noon" id="inf_noon" class="cc-input" placeholder="0">
                            </div>
                            <div class="dose-cell">
                                <label class="cc-label" style="font-size:9px;">Night</label>
                                <input type="text" name="night" id="inf_night" class="cc-input" placeholder="1">
                            </div>
                        </div>
                    </div>
                    <div class="cc-form-group">
                        <label class="cc-label">Dose</label>
                        <input type="text" name="dose" id="inf_dose" class="cc-input" placeholder="e.g. 1 Tablet">
                    </div>
                    <div class="cc-form-group">
                        <label class="cc-label">আগে / পরে</label>
                        <select name="timing" id="inf_timing" class="cc-input cc-select">
                            <option value="">-- Select --</option>
                            <option value="before">আগে</option>
                            <option value="after">পরে</option>
                            <option value="with">সাথে</option>
                            <option value="empty">খালি পেটে</option>
                        </select>
                    </div>
                    <div class="cc-form-group">
                        <label class="cc-label">কতদিন</label>
                        <input type="text" name="duration" id="inf_duration" class="cc-input" placeholder="e.g. ৭ দিন">
                    </div>
                    <div class="cc-form-group">
                        <label class="cc-label">Instruction</label>
                        <input type="text" name="instruction" id="inf_instruction" class="cc-input" placeholder="Special notes...">
                    </div>

                    <div style="display:flex;gap:8px;margin-top:4px;">
                        <button type="submit" class="cc-btn cc-btn-primary" style="flex:1;">
                            <i class="fas fa-check"></i> Save Medicine
                        </button>
                        <button type="reset" class="cc-btn cc-btn-outline cc-btn-sm">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>
                </form>

                {{-- Allergy Warning --}}
                <div class="cc-allergy-card">
                    <div class="cc-allergy-head">
                        <i class="fas fa-exclamation-triangle"></i> Allergy Warning
                    </div>
                    <div class="cc-allergy-body">
                        Review patient allergy profile before prescribing.<br>
                        <strong>Known alerts:</strong> Check medical record.
                    </div>
                </div>

                {{-- Pharmacy Status --}}
                <div class="cc-pharmacy-card">
                    <div class="cc-pharmacy-dot"></div>
                    <div class="cc-pharmacy-text">Pharmacy: In Stock — Ready for Dispatch</div>
                </div>

            </div>
        </div>

        {{-- ── RIGHT: Medicine List Card ── --}}
        <div class="cc-card">
            <div class="cc-card-head">
                <span class="cc-card-head-title"><i class="fas fa-list" style="opacity:.7;margin-right:4px;"></i> Medication List</span>
                <span style="font-size:11px;color:var(--muted);font-weight:600;">
                    Patient: <strong style="color:var(--txt);">{{ $patient->patientname ?? '' }}</strong>
                </span>
            </div>
            <div class="cc-card-body" style="padding:0;" id="medicineTableArea">
                @include('admission.admitmedicine.medicine_rows')
            </div>
        </div>

    </div>{{-- /cc-layout --}}
</div>{{-- /cc-page --}}

{{-- ══════════════════════════════════════════════════
     MODAL  (kept for editMedicineBtn compatibility)
══════════════════════════════════════════════════ --}}
<div class="modal fade" id="medicineModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="medicineForm">
            @csrf
            <input type="hidden" name="id"          id="medicine_id">
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
                            <label>Dose</label>
                            <input type="text" name="dose" id="dose" class="form-control">
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
                            <label>আগে/পরে</label>
                            <select name="timing" id="timing" class="form-control">
                                <option value="">-- Select --</option>
                                <option value="before">আগে</option>
                                <option value="after">পরে</option>
                                <option value="with">সাথে</option>
                                <option value="empty">খালি পেটে</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>কতদিন</label>
                            <input type="text" name="duration" id="duration" class="form-control" placeholder="e.g. 7 দিন">
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Instruction</label>
                            <input type="text" name="instruction" id="instruction" class="form-control">
                        </div>
                        <input type="hidden" name="order_type" id="order_type" value="admit">
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

/* ── getSlipHtml / printSlip / saveSlip / savePrintNext — UNCHANGED ── */
function getSlipHtml(){
    var rows='';var i=1;
    $('#medicineTableArea table tbody tr').each(function(){
        var row=$(this);var tds=$(this).find('td');
        if(tds.length>=7){
            rows+=admissionPrintMedicineRow(
                tds.eq(1).text().trim(),tds.eq(2).text().trim(),
                tds.eq(3).text().trim(),tds.eq(4).text().trim(),
                row.data('strength')||'',
                row.data('timing')||tds.eq(5).text().trim(),
                row.data('duration')||tds.eq(6).text().trim(),
                row.data('instruction')||''
            );i++;
        }
    });
    rows+=admissionPrintBlankMedicineRows(Math.max(0,8-(i-1)));
    return admissionPrintPrescriptionHtml({
        title:'Admit Medicine Prescription',stageTitle:'Admit Medicine Prescription',
        patientCode:$('#patientcode').val(),patientName:$('#print_patientname').val(),
        mobile:$('#print_mobile').val(),admissionDate:$('#print_admissiondate').val(),
        logoUrl:logoUrl,
        doctorName:@json($printDoctor->doctor_name ?? $printDoctor->name ?? ''),
        doctorSpeciality:@json($printDoctor->speciality ?? $printDoctor->Posting ?? ''),
        doctorRegNo:@json($printDoctor->reg_no ?? ''),
        doctorContact:@json($printDoctor->contact ?? ''),
        rows:rows
    });
}

function printSlip(){
    var html=getSlipHtml();
    var pname=$('#print_patientname').val();
    var pw=window.open('','_blank','width=1000,height=700');
    pw.document.open();pw.document.write(html);pw.document.close();
    var today=new Date().toLocaleDateString('en-GB').replace(/\//g,'-');
    pw.document.title='Admit_Medicine_'+pname+'_'+today;
    pw.onload=function(){pw.focus();pw.print();setTimeout(function(){pw.close();},500);};
}

function saveSlip(){
    $.ajax({
        url:"{{ route('admission.admedicineSaveAllAdmit') }}",type:"POST",
        data:{_token:"{{ csrf_token() }}",patientcode:$('#patientcode').val()},
        success:function(response){
            if(response.success){
                $('#ajaxMessage').html('<div class="alert alert-success">'+response.message+'</div>');
                if(typeof loadMedicineTable==='function')loadMedicineTable();
                $.ajax({
                    url:"{{ route('admission.nextStage') }}",type:"POST",
                    data:{_token:"{{ csrf_token() }}",patientcode:$('#patientcode').val()},
                    success:function(res){
                        if(res.success){
                            $('#ajaxMessage').append('<br><span class="text-success">'+res.message+'</span>');
                            setTimeout(function(){location.reload();},1500);
                        }
                    }
                });
            }else{
                $('#ajaxMessage').html('<div class="alert alert-danger">'+(response.message||'Save failed.')+'</div>');
            }
        },
        error:function(xhr){
            var msg='Save failed.';
            if(xhr.responseJSON&&xhr.responseJSON.message)msg=xhr.responseJSON.message;
            $('#ajaxMessage').html('<div class="alert alert-danger">'+msg+'</div>');
        }
    });
}

function savePrintNext(){
    $.ajax({
        url:"{{ route('admission.admedicineSaveAllAdmit') }}",type:"POST",
        data:{_token:"{{ csrf_token() }}",patientcode:$('#patientcode').val()},
        success:function(response){
            if(response.success){
                printSlip();
                $.ajax({
                    url:"{{ route('admission.nextStage') }}",type:"POST",
                    data:{_token:"{{ csrf_token() }}",patientcode:$('#patientcode').val()},
                    success:function(res){
                        if(res.success){
                            $('#ajaxMessage').html('<div class="alert alert-success">Saved & Printed. '+res.message+'</div>');
                            setTimeout(function(){location.reload();},2000);
                        }
                    }
                });
            }else{
                $('#ajaxMessage').html('<div class="alert alert-danger">'+(response.message||'Save failed.')+'</div>');
            }
        },
        error:function(){$('#ajaxMessage').html('<div class="alert alert-danger">Save failed.</div>');}
    });
}
</script>
@endsection

@push('js')
<script>
function loadMedicineTable(){
    $.ajax({
        url:"{{ route('admission.admedicineList.list') }}",type:"POST",
        data:{_token:"{{ csrf_token() }}",patientcode:$('#patientcode').val()},
        success:function(response){$('#medicineTableArea').html(response);}
    });
}

$(document).ready(function(){

    /* ── Save & Print & Next toolbar button ── */
    $(document).on('click','.btn-save-print-next',function(){
        savePrintNext();
    });

    /* ── Inline form: Add New Medicine ── */
    $('#medicineFormInline').on('submit',function(e){
        e.preventDefault();
        // map inline fields → original field names for the route
        var data={
            _token:"{{ csrf_token() }}",
            id:$('#inf_id').val(),
            patientcode:$('#patientcode').val(),
            name:$('#inf_name').val(),
            strength:$('#inf_strength').val(),
            dose:$('#inf_dose').val(),
            morning:$('#inf_morning').val(),
            noon:$('#inf_noon').val(),
            night:$('#inf_night').val(),
            timing:$('#inf_timing').val(),
            duration:$('#inf_duration').val(),
            instruction:$('#inf_instruction').val(),
            order_type:'admit',
            templeteid:$('#inf_templeteid').val()
        };
        $.ajax({
            url:"{{ route('admission.admedicineSave.save') }}",type:"POST",data:data,
            success:function(response){
                $('#ajaxMessage').html('<div class="alert alert-success">'+response.message+'</div>');
                $('#medicineFormInline')[0].reset();
                $('#inf_id').val('');
                loadMedicineTable();
            },
            error:function(){$('#ajaxMessage').html('<div class="alert alert-danger">Something went wrong.</div>');}
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
            url:"{{ route('admission.admedicineSave.save') }}",type:"POST",data:$(this).serialize(),
            success:function(response){
                $('#medicineModal').modal('hide');
                $('#ajaxMessage').html('<div class="alert alert-success">'+response.message+'</div>');
                loadMedicineTable();
            },
            error:function(){$('#ajaxMessage').html('<div class="alert alert-danger">Something went wrong.</div>');}
        });
    });

    /* ── Edit button (in table rows) ── */
    $(document).on('click','.editMedicineBtn',function(){
        $('#medicine_id').val($(this).data('id'));
        $('#name').val($(this).data('name'));
        $('#strength').val($(this).data('strength'));
        $('#dose').val($(this).data('dose'));
        $('#morning').val($(this).data('morning'));
        $('#noon').val($(this).data('noon'));
        $('#night').val($(this).data('night'));
        $('#timing').val($(this).data('timing'));
        $('#duration').val($(this).data('duration'));
        $('#instruction').val($(this).data('instruction'));
        $('#templeteid').val($(this).data('templeteid'));
        $('#order_type').val('admit');
        $('#modalTitle').text('Edit Medicine');
        $('#medicineModal').modal('show');
    });

    /* ── Delete button ── */
    $(document).on('click','.deleteMedicineBtn',function(){
        if(!confirm('Are you sure you want to delete this medicine?'))return;
        var id=$(this).data('id');
        $.ajax({
            url:"{{ route('admission.admedicine.delete') }}",type:"POST",
            data:{_token:"{{ csrf_token() }}",id:id},
            success:function(response){
                $('#ajaxMessage').html('<div class="alert alert-success">'+response.message+'</div>');
                loadMedicineTable();
            },
            error:function(){$('#ajaxMessage').html('<div class="alert alert-danger">Delete failed.</div>');}
        });
    });

});
</script>
@endpush

@push('js')
@if(session('active_page')=='admedicine')
<script>
$(document).ready(function(){
    let menu=$('a[href*="admission/admedicine"]');
    menu.addClass('active');
    menu.closest('.nav-item').addClass('menu-open');
    menu.closest('.nav-treeview').css('display','block');
});
</script>
@endif
@endpush