<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Operation Consent Form</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 14px;
            line-height: 1.6;
            margin: 40px;
            color: #000;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .header p {
            margin: 2px 0;
            font-size: 13px;
        }
        .title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
            text-decoration: underline;
        }
        table.info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.info td, table.info th {
            border: 1px solid #000;
            padding: 6px 10px;
            font-size: 14px;
        }
        table.info th {
            background: #f0f0f0;
            width: 25%;
        }
        .consent-text {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #000;
            min-height: 150px;
            text-align: justify;
        }
        .signature-row {
            margin-top: 60px;
            width: 100%;
        }
        .signature-row table {
            width: 100%;
        }
        .signature-row td {
            text-align: center;
            padding-top: 40px;
            border-top: 1px solid #000;
            font-size: 13px;
        }
        .footer {
            position: fixed;
            bottom: 20px;
            width: 100%;
            text-align: center;
            font-size: 11px;
            color: #666;
        }
        @media print {
            body { margin: 20px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:right;margin-bottom:10px;">
        <button onclick="window.print()" style="padding:8px 20px;background:#28a745;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:14px;">
            <i class="fas fa-print"></i> Print
        </button>
        <button onclick="window.close()" style="padding:8px 20px;background:#6c757d;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:14px;margin-left:5px;">
            Close
        </button>
    </div>

    <div class="header">
        <h2>Professor Clinic</h2>
        <p>Hospital & Diagnostic Center</p>
        <p>{{ $admission->address ?? '' }}</p>
    </div>

    <div class="title">INFORMED CONSENT FOR OPERATION</div>

    <table class="info">
        <tr>
            <th>Patient's Name</th>
            <td>{{ $consent->patient_name }}</td>
            <th>Age</th>
            <td>{{ $consent->age }}</td>
        </tr>
        <tr>
            <th>Father/Husband</th>
            <td>{{ $consent->father_husband_name }}</td>
            <th>Reg No</th>
            <td>{{ $admission->reg_no ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Village</th>
            <td>{{ $consent->village }}</td>
            <th>Post Office</th>
            <td>{{ $consent->post_office }}</td>
        </tr>
        <tr>
            <th>Police Station</th>
            <td>{{ $consent->police_station }}</td>
            <th>District</th>
            <td>{{ $consent->district }}</td>
        </tr>
        <tr>
            <th>Operation Name</th>
            <td colspan="3"><strong>{{ $consent->operation_name }}</strong></td>
        </tr>
        <tr>
            <th>Surgeon</th>
            <td>{{ $consent->surgeon_name }}</td>
            <th>Anesthetist</th>
            <td>{{ $consent->anesthetist ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Operation Date</th>
            <td>{{ $consent->operation_date }}</td>
            <th>Consent Date</th>
            <td>{{ $consent->consent_date }}</td>
        </tr>
    </table>

    <div class="consent-text">
        @if($consent->consent_text)
            {!! nl2br(e($consent->consent_text)) !!}
        @else
            <p>I, <strong>{{ $consent->guardian_name ? $consent->guardian_name . ' (' . $consent->relation . ' of the patient)' : 'the patient' }}</strong>, hereby give my consent to <strong>Professor Clinic</strong> and the operating surgeon <strong>{{ $consent->surgeon_name }}</strong> to perform the operation <strong>{{ $consent->operation_name }}</strong> on the patient <strong>{{ $consent->patient_name }}</strong>.</p>
            <p>I have been explained about the nature, purpose, and potential risks of the operation. I understand that there may be unforeseen circumstances that could require additional procedures. I fully understand and accept these risks.</p>
            <p>I also give consent for the administration of anesthesia as considered necessary by the anesthetist.</p>
            <p>I confirm that I have not been forced or coerced into giving this consent and I do so voluntarily.</p>
        @endif
    </div>

    <div class="signature-row">
        <table>
            <tr>
                <td style="width:33%">
                    <div style="margin-bottom:5px;font-weight:bold;">{{ $consent->guardian_name ? $consent->guardian_name . ' (' . $consent->relation . ')' : 'Patient' }}</div>
                    Signature of Patient / Guardian
                </td>
                <td style="width:33%">
                    <div style="margin-bottom:5px;font-weight:bold;">{{ $consent->surgeon_name }}</div>
                    Signature of Surgeon
                </td>
                <td style="width:33%">
                    <div style="margin-bottom:5px;font-weight:bold;">Witness</div>
                    Signature of Witness
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        This is a computer-generated consent form. Generated on {{ now()->format('d M Y, h:i A') }}
    </div>
</body>
</html>
