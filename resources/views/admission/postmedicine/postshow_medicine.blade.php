@extends('adminlte::page')

@section('title', 'Patient Medicine')

@section('content_header')
@stop

@section('content')

{{-- ════════════════════════════════════════════════════════
     CLINICAL CLARITY V2  —  Pre Surgery Medicine
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

<div id="ajaxMessage" class="px-3 pt-3 no-print"></div>

{{-- Hidden Inputs for Logic --}}
<input type="hidden" id="patientcode" value="{{ $patientcode }}">
<input type="hidden" id="print_patientname" value="{{ $patient->patientname ?? '' }}">
<input type="hidden" id="print_mobile" value="{{ $patient->mobile_no ?? '' }}">
<input type="hidden" id="print_admissiondate" value="{{ $admission ? date('d-m-Y', strtotime($admission->created_at)) : date('d-m-Y') }}">

<div class="cc-page">

    {{-- ══ STICKY HEADER ══ --}}
    <div class="cc-header no-print">
        <div class="cc-header-left">
            <a href="{{ url()->previous() }}" class="cc-back-btn">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <span class="cc-page-title">
                Medicine List Premedicine
                <span style="opacity:.6;font-weight:500;font-size:13px;margin-left:6px;">
                    — Patient Code: {{ $patientcode }}
                </span>
            </span>
        </div>
        @if(isset($admission) && $admission)
            @php
                $stageNames = [1=>'Admit Medicine',2=>'Pre Surgery',3=>'Post Surgery',4=>'Round Patient',5=>'Fresh Prescription',6=>'Release Patient'];
                $s = (int)($admission->status ?? 1);
            @endphp
            <span class="cc-stage-badge">Stage {{ $s }}: {{ $stageNames[$s] ?? '' }}</span>
        @endif
    </div>

    {{-- ══ ACTION TOOLBAR ══ --}}
    <div class="cc-toolbar no-print">
        <button type="button" class="cc-btn cc-btn-outline" id="addMedicineBtn">
            <i class="fas fa-plus-circle"></i> Add Medicine
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
                    <input type="hidden" name="order_type"   id="inf_order_type"  value="preorder">
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
                        <label class="cc-label">Days</label>
                        <input type="text" name="duration" id="inf_duration" class="cc-input" placeholder="e.g. 5">
                    </div>

                    <div style="display:flex;gap:8px;margin-top:12px;">
                        <button type="submit" class="cc-btn cc-btn-primary" style="flex:1;">
                            <i class="fas fa-check"></i> Save Medicine
                        </button>
                        <button type="reset" class="cc-btn cc-btn-outline cc-btn-sm">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>
                </form>
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
                @include('admission.premitmedicine.premedicine_rows')
            </div>
        </div>

    </div>
</div>

{{-- ══════════════════════════════════════════════════
     MODAL (Kept for editing functionality)
══════════════════════════════════════════════════ --}}
<div class="modal fade" id="medicineModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="medicineForm">
            @csrf
            <input type="hidden" name="id" id="medicine_id">
            <input type="hidden" name="patientcode" id="modal_patientcode" value="{{ $patientcode }}">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Edit Medicine</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
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
                            <label>Days</label>
                            <input type="text" name="duration" id="duration" class="form-control">
                        </div>
                        <input type="hidden" name="order_type" id="order_type" value="preorder">
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

@push('js')
@include('admission.partials.print_prescription_helpers')
<script>
var logoUrl = @json(config('app.logo_url'));

function printSlip() {
    var rows = '';
    var i = 1;

    $('#medicineTableArea table tbody tr').each(function () {
        var tds = $(this).find('td');
        if (tds.length > 0) {
            rows += admissionPrintMedicineRow(
                tds.eq(1).text().trim(),
                tds.eq(3).text().trim(),
                tds.eq(4).text().trim(),
                tds.eq(5).text().trim(),
                tds.eq(2).text().trim()
            );
            i++;
        }
    });

    rows += admissionPrintBlankMedicineRows(Math.max(0, 8 - (i - 1)));

    var patientname   = $('#print_patientname').val();
    var mobile        = $('#print_mobile').val();
    var admissiondate = $('#print_admissiondate').val();
    var patientcode   = $('#patientcode').val();

    var printWindow = window.open('', '_blank', 'width=1000,height=700');
    printWindow.document.open();
    printWindow.document.write(admissionPrintPrescriptionHtml({
        title: 'Pre Surgery Medicine Prescription',
        stageTitle: 'Pre Surgery Medicine Prescription',
        patientCode: patientcode,
        patientName: patientname,
        mobile: mobile,
        admissionDate: admissiondate,
        logoUrl: logoUrl,
        doctorName: @json($printDoctor->doctor_name ?? $printDoctor->name ?? ''),
        doctorSpeciality: @json($printDoctor->speciality ?? $printDoctor->Posting ?? ''),
        doctorRegNo: @json($printDoctor->reg_no ?? ''),
        doctorContact: @json($printDoctor->contact ?? ''),
        rows: rows
    }));
    printWindow.document.close();
    var now = new Date();
    var today = now.toLocaleDateString('en-GB').replace(/\//g, '-');
    var time = now.getHours().toString().padStart(2,'0') + '-' + now.getMinutes().toString().padStart(2,'0') + '-' + now.getSeconds().toString().padStart(2,'0');
    printWindow.document.title = 'PreSurgery_Medicine_' + patientname + '_' + today + '_' + time;
    printWindow.onload = function () {
        printWindow.focus();
        printWindow.print();
        setTimeout(function () { printWindow.close(); }, 500);
    };
}

function savePrintNext() {
    $.ajax({
        url: "{{ route('admission.premedicineSaveAllPreorder') }}",
        type: "POST",
        data: { _token: "{{ csrf_token() }}", patientcode: $('#patientcode').val() },
        success: function (response) {
            if (response.success) {
                printSlip();
                $.ajax({
                    url: "{{ route('admission.nextStage') }}",
                    type: "POST",
                    data: { _token: "{{ csrf_token() }}", patientcode: $('#patientcode').val() },
                    success: function (res) {
                        if (res.success) {
                            $('#ajaxMessage').html('<div class="alert alert-success">Saved & Printed. ' + res.message + '</div>');
                            setTimeout(function(){ location.reload(); }, 2000);
                        }
                    }
                });
            } else {
                $('#ajaxMessage').html('<div class="alert alert-danger">' + (response.message || 'Save failed.') + '</div>');
            }
        },
        error: function () {
            $('#ajaxMessage').html('<div class="alert alert-danger">Save failed.</div>');
        }
    });
}
</script>
@endpush

@push('js')
<script>
function loadMedicineTable() {
    $.ajax({
        url: "{{ route('admission.premedicineList.list') }}",
        type: "POST",
        data: { _token: "{{ csrf_token() }}", patientcode: $('#patientcode').val() },
        success: function (response) {
            $('#medicineTableArea').html(response);
        }
    });
}

$(document).ready(function () {

    /* ── Save & Print & Next toolbar button ── */
    $(document).on('click', '.btn-save-print-next', function () {
        savePrintNext();
    });

    /* ── Inline form: Add New Medicine ── */
    $('#medicineFormInline').on('submit', function(e){
        e.preventDefault();
        var data = {
            _token: "{{ csrf_token() }}",
            id: $('#inf_id').val(),
            patientcode: $('#patientcode').val(),
            name: $('#inf_name').val(),
            strength: $('#inf_strength').val(),
            dose: $('#inf_dose').val(),
            morning: $('#inf_morning').val(),
            noon: $('#inf_noon').val(),
            night: $('#inf_night').val(),
            duration: $('#inf_duration').val(),
            order_type: 'preorder',
            templeteid: $('#inf_templeteid').val()
        };
        $.ajax({
            url: "{{ route('admission.premedicineSave.save') }}",
            type: "POST",
            data: data,
            success: function(response){
                $('#ajaxMessage').html('<div class="alert alert-success">'+response.message+'</div>');
                $('#medicineFormInline')[0].reset();
                $('#inf_id').val('');
                loadMedicineTable();
            },
            error: function(){
                $('#ajaxMessage').html('<div class="alert alert-danger">Something went wrong.</div>');
            }
        });
    });

    /* ── Scroll to inline form on Add Medicine button click ── */
    $('#addMedicineBtn').on('click', function () {
        $('#inf_name').focus();
        $('html,body').animate({scrollTop:$('#inf_name').offset().top-80},300);
    });

    /* ── Modal form: Edit ── */
    $('#medicineForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: "{{ route('admission.premedicineSave.save') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function (response) {
                $('#medicineModal').modal('hide');
                $('#ajaxMessage').html('<div class="alert alert-success">' + response.message + '</div>');
                loadMedicineTable();
            },
            error: function () {
                $('#ajaxMessage').html('<div class="alert alert-danger">Something went wrong.</div>');
            }
        });
    });

    /* ── Edit button logic ── */
    $(document).on('click', '.editMedicineBtn', function () {
        $('#medicine_id').val($(this).data('id'));
        $('#name').val($(this).data('name'));
        $('#strength').val($(this).data('strength'));
        $('#dose').val($(this).data('dose'));
        $('#morning').val($(this).data('morning'));
        $('#noon').val($(this).data('noon'));
        $('#night').val($(this).data('night'));
        $('#duration').val($(this).data('duration'));
        $('#templeteid').val($(this).data('templeteid'));
        $('#order_type').val('preorder');
        $('#modalTitle').text('Edit Medicine');
        $('#medicineModal').modal('show');
    });

    /* ── Delete button logic ── */
    $(document).on('click', '.deleteMedicineBtn', function () {
        if (!confirm('Are you sure you want to delete this medicine?')) return;
        var id = $(this).data('id');
        $.ajax({
            url: "{{ route('admission.premedicine.delete') }}",
            type: "POST",
            data: { _token: "{{ csrf_token() }}", id: id },
            success: function (response) {
                $('#ajaxMessage').html('<div class="alert alert-success">' + response.message + '</div>');
                loadMedicineTable();
            },
            error: function () {
                $('#ajaxMessage').html('<div class="alert alert-danger">Delete failed.</div>');
            }
        });
    });

});
</script>
@endpush

@push('js')
@if(isset($activeMenu))
<script>
$(document).ready(function () {
    let menu = $('a[href*="{{ $activeMenu }}"]');
    menu.addClass('active');
    menu.closest('.nav-item').addClass('menu-open');
    menu.closest('.nav-treeview').css('display', 'block');
});
</script>
@endif
@endpush