@extends('adminlte::page')

@section('title', 'Patient Admission Slip')

@push('css')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@500;600;700&family=Inter:wght@500;600;700;800&display=swap">
<style id="cardioFormPrintStyles">
    :root{
        --cf-black: #000000;
        --cf-border: #000000;
        --cf-bg: #ffffff;
    }

    * { box-sizing: border-box; }

    .cf-card{
        border-radius: 6px;
        overflow: hidden;
        font-family: "Hind Siliguri", "Segoe UI", "Noto Sans Bengali", Arial, sans-serif;
    }
    .cf-card-header{
        background: #111827;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 24px;
        border-radius: 6px 6px 0 0;
    }
    .cf-card-header h3{
        color: #fff;
        font-weight: 800;
        font-size: 19px;
        margin: 0;
    }
    .btn-print{
        background: #fff;
        color: #000;
        border: 2px solid #000;
        border-radius: 4px;
        font-weight: 800;
        font-size: 15px;
        padding: 8px 24px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-print:hover{ background: #e2e8f0; }

    #printArea{
        background: #cbd5e1;
        padding: 16px;
    }

    .cf-wrapper{
        width: 210mm;
        max-width: 100%;
        min-height: 297mm;
        margin: 0 auto;
        background: #fff;
        box-shadow: 0 8px 28px rgba(0,0,0,.2);
        padding: 5mm 7mm;
        box-sizing: border-box;
        color: #000;
        font-family: "Hind Siliguri", "Segoe UI", "Noto Sans Bengali", Arial, sans-serif;
        border: 2.5pt solid #000;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    /* ---- Header ---- */
    .cf-header{
        display: flex;
        align-items: center;
        gap: 14px;
        border-bottom: 2.2pt solid #000;
        padding-bottom: 6px;
        margin-bottom: 4px;
    }
    .cf-logo{ width: 72px; height: auto; flex-shrink: 0; }
    .cf-header-center{
        flex: 1;
        text-align: center;
    }
    .cf-clinic-bn{
        font-size: 22px;
        font-weight: 900;
        color: #000;
        line-height: 1.15;
        letter-spacing: .3px;
    }
    .cf-clinic-en{
        font-size: 13.5px;
        font-weight: 800;
        color: #000;
        margin-top: 1px;
        letter-spacing: .4px;
    }
    .cf-clinic-sub{
        font-size: 11.5px;
        font-weight: 700;
        color: #111;
        margin-top: 1px;
    }

    /* ---- Main Form Title Banner ---- */
    .cf-main-title{
        background: #000;
        color: #fff;
        text-align: center;
        font-size: 16px;
        font-weight: 900;
        letter-spacing: 1px;
        padding: 4px 10px;
        margin: 4px 0 6px;
        border-radius: 2px;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* ---- Section Bars ---- */
    .cf-section-bar{
        background: #000;
        color: #fff;
        font-weight: 800;
        font-size: 13px;
        letter-spacing: .6px;
        padding: 3.5px 8px;
        margin: 6px 0 4px;
        border-radius: 2px;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        break-inside: avoid;
        page-break-inside: avoid;
    }

    /* ---- Grid Rows & Fields ---- */
    .cf-row{
        display: grid;
        gap: 6px 10px;
        margin-bottom: 5px;
        break-inside: avoid;
        page-break-inside: avoid;
    }
    .cf-row.cols-2{ grid-template-columns: 1fr 1fr; }
    .cf-row.cols-3{ grid-template-columns: 1fr 1fr 1fr; }
    .cf-row.cols-4{ grid-template-columns: 1fr 1fr 1fr 1fr; }
    .cf-row.cols-name-reg{ grid-template-columns: 2fr 1fr; }
    .cf-row.cols-2-wide{ grid-template-columns: 1.6fr 1fr; }
    .cf-row.cols-doc-ref{ grid-template-columns: 1.8fr 1.2fr; }
    .cf-row.cols-vitals{ grid-template-columns: repeat(6, 1fr); }

    .cf-field{ break-inside: avoid; page-break-inside: avoid; }
    .cf-field label{
        display: block;
        font-weight: 800;
        font-size: 11.5px;
        color: #000;
        margin-bottom: 1.5px;
        line-height: 1.2;
    }
    .cf-field input[type="text"],
    .cf-field textarea{
        width: 100%;
        box-sizing: border-box;
        border: 1.3pt solid #000;
        border-radius: 2px;
        padding: 4px 7px;
        font-family: "Hind Siliguri", "Segoe UI", Arial, sans-serif;
        font-size: 14.5px;
        font-weight: 700;
        color: #000;
        background: #fff;
        min-height: 32px;
        line-height: 1.25;
    }
    .cf-field textarea{
        min-height: 48px;
        resize: vertical;
    }

    /* ---- Signatures Box Grid ---- */
    .cf-sig-section{
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 12px;
        margin-top: 10px;
        margin-bottom: 4px;
        break-inside: avoid;
        page-break-inside: avoid;
    }
    .cf-sig-box{
        border: 1.2pt dashed #000;
        padding: 6px 8px;
        min-height: 60px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-align: center;
        background: #fff;
    }
    .cf-sig-space{ height: 32px; }
    .cf-sig-title{
        border-top: 1.2pt solid #000;
        padding-top: 3px;
        font-size: 11.5px;
        font-weight: 800;
        color: #000;
    }

    /* ---- Footer Note ---- */
    .cf-footer{
        border-top: 1.5pt solid #000;
        padding-top: 4px;
        margin-top: 6px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 11px;
        font-weight: 700;
        color: #000;
    }

    @media print{
        html, body{
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            color: #000 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            font-family: "Hind Siliguri", "Segoe UI", "Noto Sans Bengali", Arial, sans-serif !important;
        }
        .no-print, .cf-card-header, .main-footer, .main-header, .main-sidebar{ display: none !important; }
        #printArea{ padding: 0 !important; margin: 0 !important; background: #fff !important; }
        
        .cf-wrapper{
            width: 100% !important;
            max-width: 100% !important;
            height: 100% !important;
            min-height: 285mm !important;
            margin: 0 !important;
            padding: 4mm 6mm !important;
            box-shadow: none !important;
            border: 2pt solid #000 !important;
            box-sizing: border-box !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
        }
        
        .cf-header{ padding-bottom: 4px; margin-bottom: 3px; border-bottom: 2pt solid #000; }
        .cf-logo{ width: 68px; }
        .cf-clinic-bn{ font-size: 20pt !important; font-weight: 900 !important; color: #000 !important; }
        .cf-clinic-en{ font-size: 11.5pt !important; font-weight: 800 !important; color: #000 !important; }
        .cf-clinic-sub{ font-size: 9.5pt !important; font-weight: 700 !important; color: #000 !important; }

        .cf-main-title{
            background: #000 !important;
            color: #fff !important;
            font-size: 13.5pt !important;
            font-weight: 900 !important;
            padding: 3pt 8pt !important;
            margin: 3pt 0 5pt !important;
        }

        .cf-section-bar{
            background: #000 !important;
            color: #fff !important;
            font-size: 11pt !important;
            font-weight: 800 !important;
            padding: 2.5pt 6pt !important;
            margin: 5pt 0 3pt !important;
        }

        .cf-row{ gap: 4pt 7pt; margin-bottom: 4pt; }
        .cf-field label{ font-size: 9.5pt !important; font-weight: 800 !important; color: #000 !important; margin-bottom: 1pt !important; }
        .cf-field input[type="text"],
        .cf-field textarea{
            font-size: 12pt !important;
            font-weight: 800 !important;
            color: #000 !important;
            padding: 2.5pt 5pt !important;
            min-height: 20pt !important;
            border: 1.1pt solid #000 !important;
            border-radius: 0 !important;
            background: #fff !important;
        }
        .cf-field textarea{ min-height: 38pt !important; }

        .cf-sig-section{ margin-top: 8pt; margin-bottom: 2pt; gap: 8pt; }
        .cf-sig-box{ border: 1pt dashed #000 !important; min-height: 52pt !important; padding: 4pt 6pt !important; }
        .cf-sig-space{ height: 26pt !important; }
        .cf-sig-title{ font-size: 9.5pt !important; font-weight: 800 !important; border-top: 1pt solid #000 !important; }

        .cf-footer{ margin-top: 5pt; padding-top: 3pt; border-top: 1.2pt solid #000 !important; font-size: 8.5pt !important; }
        @page{ size: A4 portrait; margin: 4mm 5mm !important; }
    }

    @media (max-width: 640px){
        .cf-row.cols-2,
        .cf-row.cols-3,
        .cf-row.cols-4,
        .cf-row.cols-name-reg,
        .cf-row.cols-2-wide,
        .cf-row.cols-doc-ref,
        .cf-row.cols-vitals,
        .cf-sig-section{ grid-template-columns: 1fr; }
        .cf-wrapper{ width: 100%; padding: 10px; }
    }
</style>
@endpush

@section('content_header')
    <h1>Patient Admission Slip</h1>
@stop

@section('content')

<div class="cf-card">

    <div class="cf-card-header no-print">
        <h3><i class="fas fa-file-medical-alt mr-2"></i> Patient Admission Slip</h3>
        <button type="button" onclick="printForm()" class="btn-print">
            <i class="fas fa-print"></i> Print Admission Slip
        </button>
    </div>

    <div id="printArea">

    <div class="cf-wrapper">

        {{-- TOP HOSPITAL HEADER --}}
        <div>
            <div class="cf-header">
                <img src="{{ asset('images/PC.png') }}" alt="Hospital Logo" class="cf-logo">
                <div class="cf-header-center">
                    <div class="cf-clinic-bn">{{ config('clinic.name_bn') }}</div>
                    <div class="cf-clinic-en">{{ config('clinic.name') }}</div>
                    <div class="cf-clinic-sub">{{ config('clinic.address') }} &nbsp;|&nbsp; মোবাইল: {{ config('clinic.phone') }} &nbsp;|&nbsp; রেজিঃ নং: {{ config('clinic.reg_no') }}</div>
                </div>
            </div>

            <div class="cf-main-title">রোগী ভর্তি ফরম / PATIENT ADMISSION SLIP</div>

            {{-- SECTION 1: PATIENT INFORMATION --}}
            <div class="cf-section-bar">১. রোগীর ব্যক্তিগত বিবরণ (PATIENT INFORMATION)</div>
            
            <div class="cf-row cols-name-reg">
                <div class="cf-field">
                    <label for="patient_name">রোগীর নাম (Patient Name)</label>
                    <input type="text" id="patient_name" name="patient_name" value="{{ $patient->patientname ?? '' }}">
                </div>
                <div class="cf-field">
                    <label for="patient_code">পেশেন্ট আইডি / রেজিঃ নং (Patient ID)</label>
                    <input type="text" id="patient_code" name="patient_code" value="{{ $patient->patientcode ?? $patientcode ?? '' }}">
                </div>
            </div>

            <div class="cf-row cols-3">
                <div class="cf-field">
                    <label for="age_gender">বয়স / লিঙ্গ (Age / Gender)</label>
                    <input type="text" id="age_gender" name="age_gender" value="{{ trim(($patient->age ?? '') . ' / ' . ($patient->gender ?? ''), ' /') }}">
                </div>
                <div class="cf-field">
                    <label for="date_of_birth">জন্ম তারিখ (Date of Birth)</label>
                    <input type="text" id="date_of_birth" name="date_of_birth" value="{{ $patient->date_of_birth ?? '' }}">
                </div>
                <div class="cf-field">
                    <label for="blood_group">রক্তের গ্রুপ (Blood Group)</label>
                    <input type="text" id="blood_group" name="blood_group" value="{{ $patient->blood_group ?? '' }}">
                </div>
            </div>

            <div class="cf-row cols-3">
                <div class="cf-field">
                    <label for="contact_number">মোবাইল নম্বর (Contact Mobile)</label>
                    <input type="text" id="contact_number" name="contact_number" value="{{ $patient->mobile_no ?? '' }}">
                </div>
                <div class="cf-field">
                    <label for="father_name">পিতার নাম (Father's Name)</label>
                    <input type="text" id="father_name" name="father_name" value="{{ $patient->patientfather ?? '' }}">
                </div>
                <div class="cf-field">
                    <label for="husband_name">স্বামী / অভিভাবকের নাম (Spouse / Guardian)</label>
                    <input type="text" id="husband_name" name="husband_name" value="{{ $patient->patienthusband ?? '' }}">
                </div>
            </div>

            <div class="cf-row cols-3">
                <div class="cf-field">
                    <label for="village">গ্রাম / মহল্লা (Village / Area)</label>
                    <input type="text" id="village" name="village" value="{{ $patient->village ?? '' }}">
                </div>
                <div class="cf-field">
                    <label for="post">ডাকঘর (Post Office)</label>
                    <input type="text" id="post" name="post" value="{{ $patient->post ?? '' }}">
                </div>
                <div class="cf-field">
                    <label for="union">ইউনিয়ন / ওয়ার্ড (Union / Ward)</label>
                    <input type="text" id="union" name="union" value="{{ $patient->union ?? '' }}">
                </div>
            </div>

            <div class="cf-row cols-2">
                <div class="cf-field">
                    <label for="upozila">উপজেলা / থানা (Upozila / Thana)</label>
                    <input type="text" id="upozila" name="upozila" value="{{ $patient->upozila ?? '' }}">
                </div>
                <div class="cf-field">
                    <label for="district">জেলা (District)</label>
                    <input type="text" id="district" name="district" value="{{ $patient->district ?? '' }}">
                </div>
            </div>

            {{-- SECTION 2: ADMISSION & DOCTOR DETAILS --}}
            <div class="cf-section-bar">২. ভর্তির বিবরণ ও নির্ধারিত কেবিন/বেড (ADMISSION & ACCOMMODATION)</div>
            
            <div class="cf-row cols-3">
                <div class="cf-field">
                    <label for="admission_date">ভর্তির তারিখ (Admission Date)</label>
                    <input type="text" id="admission_date" name="admission_date" value="{{ \Carbon\Carbon::parse($admission->created_at ?? now())->format('d-m-Y') }}">
                </div>
                <div class="cf-field">
                    <label for="admission_time">ভর্তির সময় (Admission Time)</label>
                    <input type="text" id="admission_time" name="admission_time" value="{{ \Carbon\Carbon::parse($admission->created_at ?? now())->format('h:i A') }}">
                </div>
                <div class="cf-field">
                    <label for="ward_room_no">কেবিন / ওয়ার্ড / বেড নং (Cabin / Ward / Bed)</label>
                    <input type="text" id="ward_room_no" name="ward_room_no" value="{{ trim(implode(' - ', array_filter([$assignedBed->bed_name ?? $assignedBed->ward_name ?? $assignedBed->bed_type ?? '', !empty($assignedBed->bed_number) ? ('Bed: ' . $assignedBed->bed_number) : '', $admission->ward ?? ''])), ' -') }}">
                </div>
            </div>

            <div class="cf-row cols-doc-ref">
                <div class="cf-field">
                    <label for="consultant_doctor">দায়িত্বপ্রাপ্ত কনসালটেন্ট / চিকিৎসক (Consultant Doctor)</label>
                    @php
                        $docFull = trim(($printDoctor->doctor_name ?? $printDoctor->name ?? $admission->doctor_name ?? '') . (!empty($printDoctor->speciality) ? ' (' . $printDoctor->speciality . ')' : '') . (!empty($printDoctor->reg_no) ? ' [Reg: ' . $printDoctor->reg_no . ']' : ''));
                    @endphp
                    <input type="text" id="consultant_doctor" name="consultant_doctor" value="{{ $docFull }}">
                </div>
                <div class="cf-field">
                    <label for="referred_by">রেফার্ড বাই (Referred By / Contact)</label>
                    <input type="text" id="referred_by" name="referred_by" value="{{ $admission->referred_by ?? '' }}">
                </div>
            </div>

            <div class="cf-row cols-2-wide">
                <div class="cf-field">
                    <label for="reason_for_admission">ভর্তির কারণ / প্রধান উপসর্গ (Reason for Admission / Complaints)</label>
                    <input type="text" id="reason_for_admission" name="reason_for_admission" value="{{ $admission->remark ?? '' }}">
                </div>
                <div class="cf-field">
                    <label for="procedure_type">ট্রিটমেন্ট / প্রসিডিউর টাইপ (Procedure Type)</label>
                    @php
                        $selectedProc = ($procedureTypes ?? collect())->firstWhere('id', $procedureTypeId);
                    @endphp
                    <input type="text" id="procedure_type" name="procedure_type" value="{{ $selectedProc->procedure_name ?? '' }}">
                </div>
            </div>

            {{-- SECTION 3: CLINICAL EXAMINATION & VITALS --}}
            <div class="cf-section-bar">৩. প্রাথমিক স্বাস্থ্য পরীক্ষা ও পর্যবেক্ষণ (CLINICAL EXAMINATION & FINDINGS)</div>
            
            <div class="cf-row cols-2">
                <div class="cf-field">
                    <label for="vital_signs">ভাইটাল সাইন (Vital Signs: BP, Pulse, Temp, RR, SpO₂, Weight)</label>
                    <input type="text" id="vital_signs" name="vital_signs" placeholder="BP: ___/___ mmHg | Pulse: ___/min | Temp: ___°F | SpO2: ___%">
                </div>
                <div class="cf-field">
                    <label for="ecg_findings">ইসিজি ও প্রাথমিক পর্যবেক্ষণ (ECG & Initial Findings)</label>
                    <input type="text" id="ecg_findings" name="ecg_findings" placeholder="Normal / Specific Findings">
                </div>
            </div>

            <div class="cf-row cols-2">
                <div class="cf-field">
                    <label for="chest_xray_echo">ল্যাব / রেডিওলজি তদন্ত (Investigations Ordered / X-Ray / USG)</label>
                    <input type="text" id="chest_xray_echo" name="chest_xray_echo" placeholder="CBC, Blood Sugar, USG, X-Ray etc.">
                </div>
                <div class="cf-field">
                    <label for="special_instructions">বিশেষ নির্দেশনা / প্রাথমিক ডায়াগনোসিস (Provisional Diagnosis / Notes)</label>
                    <input type="text" id="special_instructions" name="special_instructions" placeholder="Provisional diagnosis or nursing instructions">
                </div>
            </div>
        </div>

        {{-- BOTTOM SECTION: SIGNATURES & FOOTER --}}
        <div>
            {{-- SECTION 4: SIGNATURES --}}
            <div class="cf-sig-section">
                <div class="cf-sig-box">
                    <div class="cf-sig-space"></div>
                    <div class="cf-sig-title">রোগী / অভিভাবকের স্বাক্ষর ও তারিখ<br><span style="font-size:10px; font-weight:600;">(Patient / Guardian Signature)</span></div>
                </div>
                <div class="cf-sig-box">
                    <div class="cf-sig-space"></div>
                    <div class="cf-sig-title">কর্তব্যরত চিকিৎসকের স্বাক্ষর ও সিল<br><span style="font-size:10px; font-weight:600;">(Duty Doctor / Consultant)</span></div>
                </div>
                <div class="cf-sig-box">
                    <div class="cf-sig-space"></div>
                    <div class="cf-sig-title">ভর্তি কর্মকর্তার স্বাক্ষর<br><span style="font-size:10px; font-weight:600;">(Admission Desk Officer)</span></div>
                </div>
            </div>

            {{-- FOOTER NOTICE --}}
            <div class="cf-footer">
                <div>বিঃ দ্রঃ চিকিৎসকের পরামর্শ ব্যতীত ঔষধ সেবন বিপজ্জনক। ক্লিনিকের অভ্যন্তরে সার্বক্ষণিক নীরবতা বজায় রাখুন।</div>
                <div>২৪ ঘন্টা জরুরী চিকিৎসা সেবা প্রদান করা হয়।</div>
            </div>
        </div>

    </div>

    </div>

</div>

@stop

@section('js')
<script>
function printForm() {
    // Preserve anything typed on screen before cloning
    document.querySelectorAll('#printArea input, #printArea textarea').forEach(function (el) {
        if (el.tagName.toLowerCase() === 'textarea') {
            el.innerHTML = el.value;
        } else {
            el.setAttribute('value', el.value);
        }
    });

    var printContents = document.getElementById('printArea').innerHTML;
    var formStyles = document.getElementById('cardioFormPrintStyles').outerHTML;

    var printWindow = window.open('', '_blank', 'width=1000,height=750');
    printWindow.document.open();
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Patient Admission Slip</title>
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@500;600;700;800&family=Inter:wght@500;600;700;800&display=swap">
            ${formStyles}
        </head>
        <body>
            ${printContents}
        </body>
        </html>
    `);
    printWindow.document.close();
    var now = new Date();
    var today = now.toLocaleDateString('en-GB').replace(/\//g, '-');
    var time = now.getHours().toString().padStart(2,'0') + '-' + now.getMinutes().toString().padStart(2,'0') + '-' + now.getSeconds().toString().padStart(2,'0');
    printWindow.document.title = 'Admission_Slip_' + today + '_' + time;

    printWindow.onload = function () {
        printWindow.focus();
        var doPrint = function () {
            printWindow.print();
            setTimeout(function () { printWindow.close(); }, 500);
        };
        if (printWindow.document.fonts && printWindow.document.fonts.ready) {
            printWindow.document.fonts.ready.then(doPrint);
        } else {
            doPrint();
        }
    };
}
</script>
@stop

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

