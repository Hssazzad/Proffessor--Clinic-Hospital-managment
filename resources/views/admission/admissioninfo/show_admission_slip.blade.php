@extends('adminlte::page')

@section('title', 'Admission Slip')

@section('content_header')
    <h1>Patient Admission Slip</h1>
@stop

@section('content')

<div class="card">

    <div class="card-header bg-primary">
        <h3 class="card-title text-white">Admission Slip</h3>
        <div class="card-tools">
            <button type="button" onclick="printSlip()" class="btn btn-success btn-sm">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <div class="card-body" id="printArea">

    <input type="hidden" id="print_patientname" value="{{ $patient->patientname ?? '' }}">

    <style>
        .slip-wrapper{
            width:100%;
            background:#fff;
            padding:20px;
        }
        .slip-header{
            text-align:center;
            border-bottom:2px solid #000;
            padding-bottom:10px;
            margin-bottom:20px;
        }
        .slip-header h2{
            margin:0;
            font-size:28px;
            font-weight:bold;
        }
        .slip-header p{
            margin:2px 0;
            font-size:14px;
        }
        .slip-title{
            text-align:center;
            font-size:22px;
            font-weight:bold;
            margin:15px 0;
            text-decoration:underline;
        }
        .section-title{
            background:#e9ecef;
            border:1px solid #000;
            padding:8px;
            font-size:16px;
            font-weight:bold;
            margin-top:15px;
        }
        .slip-table{
            width:100%;
            border-collapse:collapse;
            margin-top:10px;
        }
        .slip-table th,
        .slip-table td{
            border:1px solid #000;
            padding:8px;
            font-size:14px;
        }
        .slip-table th{
            width:20%;
            background:#f8f9fa;
        }
        .signature-row{
            margin-top:80px;
            width:100%;
        }
        .signature-box{
            width:32%;
            display:inline-block;
            text-align:center;
            vertical-align:top;
        }
        .signature-line{
            border-top:1px solid #000;
            margin-top:40px;
            padding-top:5px;
        }
        @media print{
            body{ background:#fff; }
            .no-print{ display:none !important; }
            .card{ border:none !important; box-shadow:none !important; }
            .card-header{ display:none !important; }
            @page{ size:A4; margin:10mm; }
        }
    </style>

    <div class="slip-wrapper">

        <div class="slip-header">
            <div style="display:flex; align-items:center; justify-content:center; gap:15px; flex-wrap:wrap;">
                @if(isset($logoUrl) && $logoUrl)
                    <div style="flex-shrink:0;">
                        <img src="{{ $logoUrl }}" alt="Logo" height="60">
                    </div>
                @endif
                <div>
                    <h2>Professor Clinic</h2>
                    <p>মাঝিড়া, শাজাহানপুর, বগুড়া</p>
                    <p>Mobile: 01720-039005</p>
                </div>
            </div>
        </div>

        <div class="slip-title">
            PATIENT ADMISSION SLIP
        </div>

        <div class="section-title">
            Patient Information
        </div>

        <table class="slip-table">
            <tr>
                <th>Admission No</th>
                <td>{{ $admission->admission_no ?? $admission->admissionid ?? '' }}</td>
                <th>Date</th>
                <td>{{ date('d-m-Y') }}</td>
            </tr>
            <tr>
                <th>Patient Code</th>
                <td>{{ $patient->patientcode ?? '' }}</td>
                <th>Patient Name</th>
                <td>{{ $patient->patientname ?? '' }}</td>
            </tr>
            <tr>
                <th>Age</th>
                <td>{{ $patient->age ?? '' }}</td>
                <th>Gender</th>
                <td>{{ $patient->gender ?? '' }}</td>
            </tr>
            <tr>
                <th>Mobile</th>
                <td>{{ $patient->mobile_no ?? '' }}</td>
                <th>Address</th>
                <td>{{ $patient->address ?? '' }}</td>
            </tr>
        </table>

        <div class="section-title">
            Admission Information
        </div>

        <table class="slip-table">
            <tr>
                <th>Admission Date</th>
                <td>{{ isset($admission->admission_date) ? \Carbon\Carbon::parse($admission->admission_date)->format('d-m-Y') : \Carbon\Carbon::parse($admission->created_at ?? now())->format('d-m-Y') }}</td>
                <th>Ward / Type</th>
                <td>{{ $assignedBed->bed_type ?? $admission->ward ?? '' }}</td>
            </tr>
            <tr>
                <th>Room No</th>
                <td>{{ $assignedBed->bed_number ?? $admission->room_no ?? '' }}</td>
                <th>Bed No</th>
                <td>{{ $assignedBed->bed_number ?? $admission->bed_no ?? '' }}</td>
            </tr>
            <tr>
                <th>Admission Type</th>
                <td>{{ $admission->admission_type ?? '' }}</td>
                <th>Status</th>
                <td>{{ $admission->status ?? 'Admitted' }}</td>
            </tr>
        </table>

        <div class="section-title">
            Doctor Information
        </div>

        @php
            $slipDoctorName = $printDoctor->doctor_name ?? $printDoctor->name ?? $admission->doctor_name ?? '';
            $slipDoctorDepartment = $printDoctor->speciality ?? $printDoctor->Posting ?? $admission->department ?? '';
            $slipDoctorMobile = $printDoctor->contact ?? $admission->doctor_mobile ?? '';
        @endphp
        <table class="slip-table">
            <tr>
                <th>Consultant</th>
                <td>{{ $slipDoctorName }}</td>
                <th>Department</th>
                <td>{{ $slipDoctorDepartment }}</td>
            </tr>
            <tr>
                <th>Doctor Mobile</th>
                <td>{{ $slipDoctorMobile }}</td>
                <th>Remarks</th>
                <td>{{ $admission->remark ?? $admission->remarks ?? '' }}</td>
            </tr>
        </table>

        <div class="signature-row">
            <div class="signature-box">
                <div class="signature-line">Patient / Guardian</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Prepared By</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Authorized Signature</div>
            </div>
        </div>

    </div>

</div>

</div>

@stop

@section('js')
<script>
function printSlip() {
    var printContents = document.getElementById('printArea').innerHTML;
    var patientname = document.getElementById('print_patientname').value;

    var printWindow = window.open('', '_blank', 'width=1000,height=700');
    printWindow.document.open();
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Admission Slip</title>
            <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
            <style>
                body{ font-family: Arial, sans-serif; font-size:14px; padding:20px; }
                table{ width:100%; border-collapse:collapse; }
                table th, table td{ border:1px solid #000; padding:6px; }
                .bg-light{ background:#f2f2f2; }
                h2,h4,h5{ margin-bottom:10px; }
                input[type=hidden]{ display:none; }
            </style>
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
    printWindow.document.title = 'Admission_Slip_' + patientname + '_' + today + '_' + time;
    printWindow.onload = function () {
        printWindow.focus();
        printWindow.print();
        setTimeout(function () { printWindow.close(); }, 500);
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
