@extends('adminlte::page')
@section('title', 'Discharge Patient')
@section('content_header')
    <h1 class="text-primary font-weight-bold">
        Discharge Patient - {{ $patient->patientname ?? '' }} ({{ $patientcode }})
        <span class="badge badge-info float-right" style="font-size:14px;">
            Stage: 6 - Release Patient
        </span>
    </h1>
    <a href="{{ route('admission.discharge') }}" class="btn btn-secondary btn-sm mt-2">
        Back to Discharge List
    </a>
@stop
@section('content')
<div id="ajaxMessage"></div>
<input type="hidden" id="patientcode" value="{{ $patientcode }}">
<input type="hidden" id="print_patientname" value="{{ $patient->patientname ?? '' }}">
<input type="hidden" id="print_mobile" value="{{ $patient->mobile_no ?? '' }}">
<input type="hidden" id="print_admissiondate" value="{{ $admission ? date('d-m-Y', strtotime($admission->created_at)) : date('d-m-Y') }}">
<input type="hidden" id="print_age" value="{{ $patient->age ?? '' }}">
<input type="hidden" id="print_father" value="{{ $patient->father_name ?? '' }}">
<input type="hidden" id="print_address_village" value="{{ $patient->village ?? $patient->address ?? '' }}">
<input type="hidden" id="print_address_post" value="{{ $patient->post ?? '' }}">
<input type="hidden" id="print_address_thana" value="{{ $patient->thana ?? '' }}">
<input type="hidden" id="print_address_district" value="{{ $patient->district ?? '' }}">
<input type="hidden" id="print_blood_group" value="{{ $patient->blood_group ?? '' }}">
<input type="hidden" id="print_admit_medicines" value='@json($admitMedicines ?? [])'>
<input type="hidden" id="print_pre_medicines" value='@json($preMedicines ?? [])'>
<input type="hidden" id="print_post_medicines" value='@json($postMedicines ?? [])'>
<input type="hidden" id="print_round_medicines" value='@json($roundMedicines ?? [])'>
<input type="hidden" id="print_fresh_medicines" value='@json($freshMedicines ?? [])'>
@php $td = $templateDischarge ?? null; @endphp
<input type="hidden" id="print_treatment" value="{{ optional($td)->treatment ?? '' }}">
<input type="hidden" id="print_condition" value="{{ optional($td)->condition ?? '' }}">
<input type="hidden" id="print_followup" value="{{ optional($td)->follow_up ?? '' }}">

@if(!empty($templateDischarge) && ($templateDischarge->treatment ?? $templateDischarge->condition ?? $templateDischarge->follow_up ?? false))
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card card-outline card-secondary shadow">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-file-medical mr-2"></i>
                    Template Discharge Summary
                    <small class="ml-2 text-light">(Template: {{ optional($td)->templateid ?? '' }})</small>
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($td->treatment)
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-info"><i class="fas fa-pills"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-muted font-weight-bold">Treatment Given</span>
                                <span class="info-box-number" style="font-size:13px;white-space:pre-wrap;">{{ $td->treatment }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($td->condition)
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-success"><i class="fas fa-heartbeat"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-muted font-weight-bold">Condition at Discharge</span>
                                <span class="info-box-number" style="font-size:13px;white-space:pre-wrap;">{{ $td->condition }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($td->follow_up)
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-warning"><i class="fas fa-calendar-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-muted font-weight-bold">Follow-up Advice</span>
                                <span class="info-box-number" style="font-size:13px;white-space:pre-wrap;">{{ $td->follow_up }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-danger shadow">
            <div class="card-header"><h5>Admit Medicines</h5></div>
            <div class="card-body p-0">
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr><th>#</th><th>Medicine</th><th>Strength</th><th>Morning</th><th>Noon</th><th>Night</th></tr>
                    </thead>
                    <tbody>
                        @forelse($admitMedicines as $i => $m)
                            <tr><td>{{ $i+1 }}</td><td>{{ $m->name }}</td><td>{{ $m->strength }}</td><td>{{ $m->morning }}</td><td>{{ $m->noon }}</td><td>{{ $m->night }}</td></tr>
                        @empty
                            <tr><td colspan="6" class="text-muted">No admit medicines.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="card card-outline card-warning shadow">
            <div class="card-header"><h5>Pre Surgery Medicines</h5></div>
            <div class="card-body p-0">
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr><th>#</th><th>Medicine</th><th>Strength</th><th>Dose</th><th>Morning</th><th>Noon</th><th>Night</th></tr>
                    </thead>
                    <tbody>
                        @forelse($preMedicines as $i => $m)
                            <tr><td>{{ $i+1 }}</td><td>{{ $m->name }}</td><td>{{ $m->strength }}</td><td>{{ $m->morning }}</td><td>{{ $m->noon }}</td><td>{{ $m->night }}</td></tr>
                        @empty
                            <tr><td colspan="6" class="text-muted">No pre surgery medicines.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="card card-outline card-info shadow">
            <div class="card-header"><h5>Post Surgery Medicines</h5></div>
            <div class="card-body p-0">
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr><th>#</th><th>Medicine</th><th>Strength</th><th>Dose</th><th>Morning</th><th>Noon</th><th>Night</th></tr>
                    </thead>
                    <tbody>
                        @forelse($postMedicines as $i => $m)
                            <tr><td>{{ $i+1 }}</td><td>{{ $m->name }}</td><td>{{ $m->strength }}</td><td>{{ $m->morning }}</td><td>{{ $m->noon }}</td><td>{{ $m->night }}</td></tr>
                        @empty
                            <tr><td colspan="6" class="text-muted">No post surgery medicines.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="card card-outline card-success shadow">
            <div class="card-header"><h5>Round Patient Medicines</h5></div>
            <div class="card-body p-0">
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr><th>#</th><th>Medicine</th><th>Strength</th><th>Dose</th><th>Morning</th><th>Noon</th><th>Night</th></tr>
                    </thead>
                    <tbody>
                        @forelse($roundMedicines as $i => $m)
                            <tr><td>{{ $i+1 }}</td><td>{{ $m->name }}</td><td>{{ $m->strength }}</td><td>{{ $m->morning }}</td><td>{{ $m->noon }}</td><td>{{ $m->night }}</td></tr>
                        @empty
                            <tr><td colspan="6" class="text-muted">No round patient medicines.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="card card-outline card-primary shadow">
            <div class="card-header"><h5>Fresh Prescription Medicines</h5></div>
            <div class="card-body p-0">
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr><th>#</th><th>Medicine</th><th>Strength</th><th>Dose</th><th>Morning</th><th>Noon</th><th>Night</th></tr>
                    </thead>
                    <tbody>
                        @forelse($freshMedicines as $i => $m)
                            <tr><td>{{ $i+1 }}</td><td>{{ $m->name }}</td><td>{{ $m->strength }}</td><td>{{ $m->morning }}</td><td>{{ $m->noon }}</td><td>{{ $m->night }}</td></tr>
                        @empty
                            <tr><td colspan="6" class="text-muted">No fresh prescription medicines.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="row mt-3">
    <div class="col-md-12 text-center">
        <button type="button" onclick="printSlip()" class="btn btn-warning btn-lg mr-2">
            <i class="fas fa-print"></i> Print Slip
        </button>
        <button type="button" onclick="doDischarge()" class="btn btn-danger btn-lg">
            <i class="fas fa-sign-out-alt"></i> Discharge Patient
        </button>
    </div>
</div>
<div id="printArea" style="display:none;"></div>
<style>
@media print {
    body * { visibility: hidden !important; }
    #printArea, #printArea * { visibility: visible !important; }
    #printArea {
        display: block !important;
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
    }
    .page {
        page-break-after: always !important;
        page-break-inside: avoid !important;
        display: block !important;
        width: 100% !important;
        min-height: 297mm !important;
        padding: 15mm 20mm !important;
        box-sizing: border-box !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        font-family: "SolaimanLipi", "Kalpurush", "Siyam Rupali", Arial, sans-serif !important;
    }
    .page:last-child {
        page-break-after: auto !important;
    }
    .print-header {
        border-bottom: 2px solid #2e7d32 !important;
        padding-bottom: 8px !important;
        margin-bottom: 6px !important;
    }
}
@page {
    size: A4 portrait;
    margin: 10mm;
}
</style>
@endsection
@section('js')
<script>
var logoUrl = @json(config('app.logo_url'));

function printSlip() {
    var patientcode = $('#patientcode').val() || '';
    var patientname = $('#print_patientname').val() || '';
    var mobile = $('#print_mobile').val() || '';
    var admissiondate = $('#print_admissiondate').val() || '';
    var age = $('#print_age').val() || '';
    var father = $('#print_father').val() || '';
    var village = $('#print_address_village').val() || '';
    var post = $('#print_address_post').val() || '';
    var thana = $('#print_address_thana').val() || '';
    var district = $('#print_address_district').val() || '';
    var bloodGroup = $('#print_blood_group').val() || '';

    function parseMed(id) {
        var v = $(id).val();
        if (!v || v === 'null') return [];
        try { return JSON.parse(v); } catch (e) { return []; }
    }

    var admitMedicines = parseMed('#print_admit_medicines');
    var preMedicines = parseMed('#print_pre_medicines');
    var postMedicines = parseMed('#print_post_medicines');
    var roundMedicines = parseMed('#print_round_medicines');
    var freshMedicines = parseMed('#print_fresh_medicines');

    var treatment = document.getElementById('print_treatment').value;
    var conditionAtDischarge = document.getElementById('print_condition').value;
    var followUp = document.getElementById('print_followup').value;

    function fmtDate(d) {
        var dd = String(d.getDate()).padStart(2, '0');
        var mm = String(d.getMonth() + 1).padStart(2, '0');
        var yyyy = d.getFullYear();
        return dd + '-' + mm + '-' + yyyy;
    }
    var dischargeDate = fmtDate(new Date());

    function dotted(val, width) {
        width = width || 200;
        return '<span style="display:inline-block;min-width:' + width + 'px;border-bottom:1px dotted #333;text-align:center;padding:0 4px;font-weight:bold;">' + (val || '') + '</span>';
    }

    function spacer(h) {
        return '<div style="height:' + (h || 20) + 'px;"></div>';
    }

    function pageHeader() {
        var logoHtml = logoUrl ? '<td style="width:65px;border:none;vertical-align:middle;text-align:center;"><img src="' + logoUrl + '" style="max-height:55px;max-width:55px;"></td>' : '';
        return '<div class="print-header">' +
            '<table style="width:100%;border:none;border-collapse:collapse;"><tr>' +
            logoHtml +
            '<td style="border:none;text-align:center;vertical-align:middle;">' +
            '<div style="font-size:22px;font-weight:bold;color:#1b5e20;letter-spacing:2px;">প্রফেসর ক্লিনিক</div>' +
            '<div style="font-size:12px;color:#333;">মাঝিড়া, শাজাহানপুর, বগুড়া।</div>' +
            '<div style="font-size:11px;color:#555;">মোবাইল: ০১৭২০-০৩৯০০৫, ০১৭২০-০৩৯০০৬, ০১৭২০-০৩৯০০৭, ০১৭২০-০৩৯০০৮</div>' +
            '</td></tr></table></div>';
    }

    function titleBar(text) {
        return '<div style="background:#2e7d32;color:#fff;text-align:center;padding:6px 0;font-size:16px;font-weight:bold;margin:8px 0 14px 0;border-radius:4px;">' + text + '</div>';
    }

    function medTable(title, items) {
        if (!items || items.length === 0) return '';
        var rows = '';
        for (var i = 0; i < items.length; i++) {
            var m = items[i];
            rows += '<tr>' +
                '<td style="border:1px solid #2e7d32;padding:3px 5px;text-align:center;">' + (i + 1) + '</td>' +
                '<td style="border:1px solid #2e7d32;padding:3px 5px;">' + (m.name || '') + '</td>' +
                '<td style="border:1px solid #2e7d32;padding:3px 5px;text-align:center;">' + (m.strength || '') + '</td>' +
                '<td style="border:1px solid #2e7d32;padding:3px 5px;text-align:center;">' + (m.morning || '') + '</td>' +
                '<td style="border:1px solid #2e7d32;padding:3px 5px;text-align:center;">' + (m.noon || '') + '</td>' +
                '<td style="border:1px solid #2e7d32;padding:3px 5px;text-align:center;">' + (m.night || '') + '</td>' +
                '</tr>';
        }
        return '<div style="margin-bottom:10px;">' +
            '<div style="background:#2e7d32;color:#fff;padding:4px 10px;font-size:13px;font-weight:bold;">' + title + '</div>' +
            '<table style="width:100%;border-collapse:collapse;font-size:12px;border:1px solid #2e7d32;">' +
            '<thead><tr style="background:#e8f5e9;">' +
            '<th style="border:1px solid #2e7d32;padding:4px 5px;text-align:center;">#</th>' +
            '<th style="border:1px solid #2e7d32;padding:4px 5px;">ঔষধের নাম</th>' +
            '<th style="border:1px solid #2e7d32;padding:4px 5px;text-align:center;">Strength</th>' +
            '<th style="border:1px solid #2e7d32;padding:4px 5px;text-align:center;">সকাল</th>' +
            '<th style="border:1px solid #2e7d32;padding:4px 5px;text-align:center;">দুপুর</th>' +
            '<th style="border:1px solid #2e7d32;padding:4px 5px;text-align:center;">রাত</th>' +
            '</tr></thead>' +
            '<tbody>' + rows + '</tbody></table></div>';
    }

    var medHtml = medTable('Admit Medicines (ভর্তির ঔষধ)', admitMedicines) +
        medTable('Pre Surgery Medicines (অপারেশন পূর্বের ঔষধ)', preMedicines) +
        medTable('Post Surgery Medicines (অপারেশন পরবর্তী ঔষধ)', postMedicines) +
        medTable('Round Patient Medicines (রাউন্ডের ঔষধ)', roundMedicines) +
        medTable('Fresh Prescription Medicines (Fresh Prescription)', freshMedicines);

    var dischargeHtml = '';
    if (treatment || conditionAtDischarge || followUp) {
        dischargeHtml = '<div style="margin-bottom:12px;border:1px solid #2e7d32;border-radius:4px;overflow:hidden;">' +
            '<div style="background:#2e7d32;color:#fff;padding:4px 10px;font-size:13px;font-weight:bold;">ছাড় পত্রের সারসংক্ষেপ (Discharge Summary)</div>' +
            '<div style="padding:8px 10px;font-size:12px;line-height:1.8;">' +
            (treatment ? '<div><strong>প্রদত্ত চিকিৎসা:</strong> ' + treatment + '</div>' : '') +
            (conditionAtDischarge ? '<div><strong>ছাড়ের সময় অবস্থা:</strong> ' + conditionAtDischarge + '</div>' : '') +
            (followUp ? '<div><strong>পরবর্তী পরামর্শ:</strong> ' + followUp + '</div>' : '') +
            '</div></div>';
    }

    var page1 = '<div class="page">' +
        pageHeader() +
        titleBar('ছাড় পত্র') +
        '<div style="font-size:12px;line-height:2.2;">' +
        '<div style="margin-bottom:8px;">' +
        '<span style="font-weight:bold;">ওয়ার্ড/কেবিন নং-</span> ' + dotted('', 120) +
        '<span style="margin-left:20px;font-weight:bold;">রেজিঃ নং-</span> ' + dotted(patientcode, 120) +
        '</div>' +
        '<div><span style="font-weight:bold;">প্রত্যয়ন করা যাচ্ছে যে,</span> ' + dotted(patientname, 250) + '</div>' +
        '<div><span style="font-weight:bold;">বয়স</span> ' + dotted(age, 80) + ' <span style="font-weight:bold;">পিতা/স্বামী</span> ' + dotted(father, 200) + '</div>' +
        '<div><span style="font-weight:bold;">ঠিকানা : গ্রাম</span> ' + dotted(village, 150) + ' <span style="font-weight:bold;">পোষ্ট</span> ' + dotted(post, 120) + '</div>' +
        '<div style="margin-left:68px;"><span style="font-weight:bold;">থানা</span> ' + dotted(thana, 150) + ' <span style="font-weight:bold;">জেলা</span> ' + dotted(district, 150) + '</div>' +
        '<div><span style="font-weight:bold;">অত্র হাসপাতালে</span> ' + dotted(admissiondate, 120) + ' <span style="font-weight:bold;">তারিখ হতে</span> ' + dotted(dischargeDate, 120) + ' <span style="font-weight:bold;">তারিখ</span></div>' +
        '<div><span style="font-weight:bold;">পর্যন্ত চিকিৎসাধীন ছিলেন। তিনি</span> ' + dotted('', 250) + '</div>' +
        '<div><span style="font-weight:bold;">রোগে ভুগছিলেন। তিনি নিম্নোক্ত চিকিৎসকের অধীনে চিকিৎসাধীন ছিলেন।</span></div>' +
        spacer(30) +
        '<div style="text-align:right;margin-top:20px;">' +
        '<div style="font-size:13px;font-weight:bold;margin-bottom:5px;">কর্তৃপক্ষ / চিকিৎসকের স্বাক্ষর</div>' +
        '<div><span style="font-weight:bold;">তাং-</span> ' + dotted(dischargeDate, 120) + '</div>' +
        '<div style="margin-top:8px;border:1px solid #333;width:200px;height:50px;float:right;"></div>' +
        '</div>' +
        '<div style="clear:both;"></div>' +
        '</div>' +
        '</div>';

    function opRow(label, secondLine) {
        var extra = secondLine ? '<br>' + secondLine : '';
        return '<tr>' +
            '<td style="border:2px solid #2e7d32;padding:6px 10px;font-weight:bold;font-size:13px;background:#e8f5e9;width:200px;">' + label + extra + '</td>' +
            '<td style="border:2px solid #2e7d32;padding:6px 10px;min-height:35px;">' + dotted('', 350) + '</td>' +
            '</tr>';
    }
    var page2 = '<div class="page">' +
        pageHeader() +
        titleBar('অপারেশন নোট') +
        '<table style="width:100%;border-collapse:collapse;margin-top:10px;">' +
        opRow('অপারেশন নাম ও তাং') +
        opRow('অপারেশন পদ্ধতি ও', 'প্রাপ্ত তথ্যাবলী') +
        opRow('সার্জন') +
        opRow('এ্যানেসথেটিষ্ট') +
        opRow('এ্যাসিসট্যান্ট') +
        opRow('এ্যাসিসট্যান্ট') +
        '</table>' +
        '</div>';

    var page3 = '<div class="page">' +
        pageHeader() +
        titleBar('প্রদত্ত চিকিৎসা পরামর্শ') +
        '<div style="border:1px dashed #2e7d32;height:80px;margin-bottom:14px;"></div>' +
        '<div style="background:#2e7d32;color:#fff;padding:4px 10px;font-size:14px;font-weight:bold;margin-bottom:8px;display:inline-block;border-radius:4px;">পরীক্ষা নিরীক্ষা</div>' +
        '<div style="border:1px dashed #2e7d32;height:60px;margin-bottom:14px;"></div>' +
        '<div style="font-size:12px;margin-bottom:14px;">' +
        '<span style="font-weight:bold;">রক্ত গ্রুপ</span> ' + dotted(bloodGroup, 100) +
        '<span style="margin-left:20px;font-weight:bold;">ড্রাগ এ্যালাজী</span> ' + dotted('', 200) +
        '</div>' +
        '<hr style="border-top:1px solid #2e7d32;margin:10px 0;">' +
        '<div style="font-size:13px;font-weight:bold;color:#2e7d32;margin-bottom:8px;">চিকিৎসা প্রদানের বিবরণ (Medicine Records)</div>' +
        dischargeHtml +
        medHtml +
        '</div>';

    var page4 = '<div class="page">' +
        pageHeader() +
        titleBar('চিকিৎসা পরামর্শ ও উপদেশাবলী') +
        '<div style="border:1px dashed #2e7d32;height:100px;margin-bottom:16px;"></div>' +
        '<div style="background:#2e7d32;color:#fff;padding:6px 10px;font-size:14px;font-weight:bold;margin-bottom:10px;border-radius:4px;">ক্লিনিকের বিশেষ আকর্ষণ :</div>' +
        '<ul style="list-style:none;padding:0;margin:0;font-size:13px;line-height:2;">' +
        '<li style="padding:3px 0;">► ২৪ ঘণ্টা ইমার্জেন্সী সার্ভিস প্রদান।</li>' +
        '<li style="padding:3px 0;">► দিবা-রাত্রি সব সময় সিজারসহ যেকোন অপারেশনের সু-ব্যবস্থা।</li>' +
        '<li style="padding:3px 0;">► মহিলা এম.বি.বি.এস ডাক্তার দ্বারা নরমাল ডেলিভারির সু-ব্যবস্থা।</li>' +
        '<li style="padding:3px 0;">► অন্তঃসত্তা মহিলাদের জন্য চেক-আপের বিশেষ সুবিধাদি প্রদান।</li>' +
        '</ul>' +
        '<div style="margin-top:30px;font-size:12px;text-align:center;color:#555;border-top:1px solid #2e7d32;padding-top:10px;">' +
        'বিঃ দ্রঃ চিকিৎসকের পরামর্শ ব্যতীত ঔষধ সেবন বিপজ্জনক হতে পারে।' +
        '</div>' +
        '</div>';

    var printHtml = page1 + page2 + page3 + page4;

    var printArea = document.getElementById('printArea');
    printArea.innerHTML = printHtml;
    setTimeout(function () { window.print(); }, 300);
    setTimeout(function () { printArea.innerHTML = ''; }, 2000);
}
function doDischarge() {
    if (!confirm('Discharge this patient?')) return;
    $.ajax({
        url: "{{ route('admission.discharge.do') }}",
        type: "POST",
        data: { _token: "{{ csrf_token() }}", patientcode: $('#patientcode').val() },
        success: function (response) {
            if (response.success) {
                $('#ajaxMessage').html('<div class="alert alert-success">' + response.message + '</div>');
                setTimeout(function(){ window.location.href = "{{ route('admission.discharge') }}"; }, 2000);
            } else {
                $('#ajaxMessage').html('<div class="alert alert-danger">' + response.message + '</div>');
            }
        },
        error: function (xhr) {
            var msg = 'Discharge failed.';
            if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
            $('#ajaxMessage').html('<div class="alert alert-danger">' + msg + '</div>');
        }
    });
}
</script>
@endsection
