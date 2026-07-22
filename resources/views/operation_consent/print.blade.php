
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<title>Operation Consent - {{ $admission->patientname ?? '' }}</title>
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    * { box-sizing: border-box; }
    body {
        font-family: 'Hind Siliguri', sans-serif;
        color: #0f3d8a;
        margin: 0;
        padding: 0;
        font-size: 15px;
    }
    .oc-sheet {
        max-width: 800px;
        margin: 20px auto;
        padding: 25px 35px;
        border: 1px solid #0f3d8a;
    }
    .oc-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 3px solid #0f3d8a;
        padding-bottom: 8px;
        margin-bottom: 14px;
    }
    .oc-header-left { display: flex; align-items: center; gap: 10px; }
    .oc-logo {
        width: 46px; height: 46px; border-radius: 50%;
        border: 2px solid #0f3d8a; display: flex;
        align-items: center; justify-content: center;
        font-weight: 700; font-size: 22px; flex-shrink: 0;
    }
    .oc-clinic-name-bn { font-size: 24px; font-weight: 700; line-height: 1.1; }
    .oc-clinic-name-en { font-size: 18px; font-weight: 600; color: #0f3d8a; }
    .oc-header-right { text-align: right; font-size: 12.5px; line-height: 1.5; }

    .oc-title {
        text-align: center;
        font-weight: 700;
        font-size: 19px;
        letter-spacing: 1px;
        margin: 10px 0 18px;
        text-transform: uppercase;
    }

    .oc-row { display: flex; gap: 24px; margin-bottom: 9px; }
    .oc-field { flex: 1; display: flex; align-items: baseline; gap: 6px; }
    .oc-field.full { flex: 1 1 100%; }
    .oc-label { font-weight: 600; white-space: nowrap; }
    .oc-value {
        flex: 1;
        border-bottom: 1px dotted #0f3d8a;
        min-height: 18px;
        padding: 0 4px 2px;
    }

    .oc-note-box {
        border: 1px dotted #0f3d8a;
        min-height: 50px;
        padding: 6px 8px;
        margin-top: 4px;
        white-space: pre-wrap;
    }

    .oc-consent-title {
        text-align: center;
        margin: 22px 0 10px;
        font-weight: 700;
        font-size: 16px;
    }
    .oc-consent-title span {
        border-bottom: 2px solid #0f3d8a;
        padding: 2px 14px;
    }
    .oc-consent-text {
        text-align: justify;
        line-height: 1.8;
        font-size: 14.5px;
        margin-bottom: 30px;
    }

    .oc-signatures {
        display: flex;
        justify-content: space-between;
        margin-top: 50px;
    }
    .oc-sign-box { text-align: center; width: 45%; }
    .oc-sign-line {
        border-top: 1px solid #0f3d8a;
        padding-top: 5px;
        font-size: 13px;
        font-weight: 600;
    }
    .oc-sign-name { font-size: 14px; margin-bottom: 28px; min-height: 16px; }

    .oc-actions {
        max-width: 800px;
        margin: 0 auto 10px;
        text-align: right;
    }
    .oc-actions button, .oc-actions a {
        font-family: 'Hind Siliguri', sans-serif;
        font-size: 14px;
        padding: 8px 18px;
        border-radius: 4px;
        border: 1px solid #0f3d8a;
        background: #0f3d8a;
        color: #fff;
        cursor: pointer;
        text-decoration: none;
        margin-left: 8px;
        display: inline-block;
    }
    .oc-actions a.secondary { background: #fff; color: #0f3d8a; }

    @media print {
        .oc-actions { display: none; }
        .oc-sheet { border: none; margin: 0; max-width: 100%; }
        body { font-size: 13px; }
    }
</style>
</head>
<body>

    <div class="oc-actions">
        <a href="{{ route('operation.consent.search') }}" class="secondary">তালিকায় ফিরুন</a>
        <button onclick="window.print()">প্রিন্ট করুন</button>
    </div>

    <div class="oc-sheet">

        <div class="oc-header">
            <div class="oc-header-left">
                <div class="oc-logo">P</div>
                <div>
                    <div class="oc-clinic-name-bn">প্রফেসর ক্লিনিক</div>
                    <div class="oc-clinic-name-en">Professor Clinic</div>
                </div>
            </div>
            <div class="oc-header-right">
                রেজি. নং: ____________<br>
                ঠিকানা, ___________<br>
                মোবাইলঃ ____________
            </div>
        </div>

        <div class="oc-title">Admission Record</div>

        <div class="oc-row">
            <div class="oc-field">
                <span class="oc-label">ভর্তির তাং:</span>
                <span class="oc-value">{{ $admission->admission_date ? date('d-m-Y', strtotime($admission->admission_date)) : '' }}</span>
            </div>
            <div class="oc-field">
                <span class="oc-label">ওয়ার্ড নং:</span>
                <span class="oc-value">{{ $consent->ward_no }}</span>
            </div>
        </div>

        <div class="oc-row">
            <div class="oc-field">
                <span class="oc-label">ছুটির তাং:</span>
                <span class="oc-value">{{ $admission->discharge_date ? date('d-m-Y', strtotime($admission->discharge_date)) : '' }}</span>
            </div>
            <div class="oc-field">
                <span class="oc-label">বেড নং:</span>
                <span class="oc-value">{{ $consent->bed_no }}</span>
            </div>
            <div class="oc-field">
                <span class="oc-label">রেজিঃ নং:</span>
                <span class="oc-value">{{ $admission->patientcode }}</span>
            </div>
        </div>

        <div class="oc-row">
            <div class="oc-field">
                <span class="oc-label">পুরুষ/মহিলা:</span>
                <span class="oc-value">{{ $admission->gender }}</span>
            </div>
        </div>

        <div class="oc-row">
            <div class="oc-field full">
                <span class="oc-label">রোগীর নাম:</span>
                <span class="oc-value">{{ $admission->patientname }}</span>
            </div>
        </div>

        <div class="oc-row">
            <div class="oc-field">
                <span class="oc-label">পিতার/স্বামীর নাম:</span>
                <span class="oc-value">{{ $admission->patientfather ?: $admission->patienthusband }}</span>
            </div>
            <div class="oc-field">
                <span class="oc-label">বয়স:</span>
                <span class="oc-value">{{ $admission->age }}</span>
            </div>
        </div>

        <div class="oc-row">
            <div class="oc-field">
                <span class="oc-label">পো:</span>
                <span class="oc-value">{{ $consent->post_office }}</span>
            </div>
            <div class="oc-field">
                <span class="oc-label">থানা:</span>
                <span class="oc-value">{{ $admission->upozila }}</span>
            </div>
            <div class="oc-field">
                <span class="oc-label">গ্রাম:</span>
                <span class="oc-value">{{ $admission->village }}</span>
            </div>
        </div>

        <div class="oc-row">
            <div class="oc-field">
                <span class="oc-label">রোগের নাম:</span>
                <span class="oc-value">{{ $consent->disease_name }}</span>
            </div>
            <div class="oc-field">
                <span class="oc-label">জেলা:</span>
                <span class="oc-value">{{ $admission->district }}</span>
            </div>
        </div>

        <div class="oc-row">
            <div class="oc-field full">
                <span class="oc-label">অপারেশনের নাম:</span>
                <span class="oc-value">{{ $consent->operation_name }}</span>
            </div>
        </div>

        <div class="oc-row">
            <div class="oc-field">
                <span class="oc-label">অপারেশনের তাং:</span>
                <span class="oc-value">{{ $consent->operation_date ? date('d-m-Y', strtotime($consent->operation_date)) : '' }}</span>
            </div>
            <div class="oc-field">
                <span class="oc-label">সময়:</span>
                <span class="oc-value">{{ $consent->operation_time }}</span>
            </div>
        </div>

        <div class="oc-row">
            <div class="oc-field full">
                <span class="oc-label">সার্জন:</span>
                <span class="oc-value">{{ $consent->surgeon_name }}</span>
            </div>
        </div>
        <div class="oc-row">
            <div class="oc-field full">
                <span class="oc-label">সহযোগী সার্জন:</span>
                <span class="oc-value">{{ $consent->assistant_surgeon }}</span>
            </div>
        </div>
        <div class="oc-row">
            <div class="oc-field full">
                <span class="oc-label">অজ্ঞানকারী ডাক্তার:</span>
                <span class="oc-value">{{ $consent->anesthesiologist }}</span>
            </div>
        </div>

        <div class="oc-row">
            <div class="oc-field full" style="flex-direction:column; align-items:stretch;">
                <span class="oc-label">অপারেশন নোট:</span>
                <div class="oc-note-box">{{ $consent->operation_note }}</div>
            </div>
        </div>

        <div class="oc-consent-title"><span>সম্মতি</span></div>
        <div class="oc-consent-text">
            আমি ডাক্তার সাহেবের সঙ্গে আলোচনা করিয়া আমার / রোগীর রোগ এবং অস্ত্রোপচারের ঝুঁকি সম্পর্কে অবগত হইয়া সম্পূর্ণ স্ব-ইচ্ছায় এবং কোন প্রকার ভয় ও প্রলোভন ছাড়াই অজ্ঞান করিয়া অস্ত্রোপচার করিতে রাজি আছি। অস্ত্রোপচারে ইচ্ছাকৃত ভুল ছাড়া অন্য কোন দুর্ঘটনার জন্য ক্লিনিক কর্তৃপক্ষ বা ডাক্তার এবং তার সহকর্মীরা দায়ী থাকিবেন না।
        </div>

        <div class="oc-signatures">
            <div class="oc-sign-box">
                <div class="oc-sign-name">{{ $consent->signatory_name }}</div>
                <div class="oc-sign-line">
                    রোগী/অভিভাবকের স্বাক্ষর
                    @if($consent->signatory_relation)
                        ({{ $consent->signatory_relation }})
                    @endif
                </div>
            </div>
            <div class="oc-sign-box">
                <div class="oc-sign-name">{{ $consent->doctor_signature_name ?: $admission->doctor_name }}</div>
                <div class="oc-sign-line">ডাক্তারের স্বাক্ষর</div>
            </div>
        </div>

    </div>

</body>
</html>
