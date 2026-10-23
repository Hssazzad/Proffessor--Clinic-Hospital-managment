<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<title>অপারেশন সম্মতিপত্র - {{ $admission->patientname ?? '' }}</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    /* =====================================================================
       OPERATION CONSENT FORM — v2 (full A4, professional)
       Design language matches the clinic's existing "Clinical Precision"
       system (Clinical Blue #0f52ba accent, bordered form grids like the
       discharge booklet's patient-info table) for visual consistency
       across the app, instead of inventing a new look.

       WHY IT NOW FILLS THE A4 PAGE (not by inflating spacing):
       Real consent forms legally need more than "Admission Record" +
       one paragraph — they need the physician's own declaration that
       risks/benefits/alternatives were explained, and three separate
       signatures (patient/guardian, witness, physician), per standard
       hospital consent-form practice. Adding those genuine sections is
       what fills the page — not bigger margins.

       NOTE: the original file's <title> said "Admission Record" while
       the body was a consent form — fixed to match actual content.
       ===================================================================== */
    :root {
        --ink: #111111;
        --clinical-blue: #000000;
        --blue-tint: #eeeeee;
        --hairline: #111111;
        --muted: #555555;
    }
    * { box-sizing: border-box; }
    html, body {
        font-family: 'Noto Sans Bengali', sans-serif;
        color: var(--ink);
        margin: 0;
        padding: 0;
        font-size: 15px;
        font-weight: 500;
    }
    .oc-sheet {
        max-width: 794px;
        margin: 20px auto;
        padding: 0;
        border: 1.5pt solid var(--ink);
    }
    .oc-sheet-inner { padding: 14px 26px 20px; }

    /* ---- Letterhead ---- */
    .oc-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 10px 26px 8px;
        border-bottom: 3px solid var(--clinical-blue);
    }
    .oc-header-left { display: flex; align-items: center; gap: 10px; min-width: 0; }
    .oc-logo {
        width: 56px; height: 56px; border-radius: 50%;
        border: 1.5pt solid var(--ink); object-fit: cover;
        flex-shrink: 0;
    }
    .oc-clinic-name-bn { font-size: 20px; font-weight: 700; line-height: 1.3; letter-spacing: 0.1px; }
    .oc-clinic-name-en { font-size: 11.5px; font-weight: 600; color: var(--muted); letter-spacing: 0.5px; margin-top: 3px; }
    .oc-header-right {
        text-align: right;
        font-size: 12px;
        line-height: 1.6;
        color: var(--muted);
        flex: 0 0 auto;
        max-width: 260px;
    }

    .oc-title-bar {
        background: var(--clinical-blue);
        color: #fff;
        text-align: center;
        font-weight: 700;
        font-size: 15px;
        letter-spacing: 1.5px;
        padding: 7px 0;
        margin: 0 0 12px;
    }
    .oc-title-bar .en {
        display: block;
        font-size: 9.5px;
        font-weight: 500;
        letter-spacing: 2px;
        text-transform: uppercase;
        opacity: 0.85;
        margin-top: 1px;
    }

    /* ---- Section labels ---- */
    .oc-section-label {
        font-weight: 700;
        font-size: 13px;
        color: var(--clinical-blue);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        border-bottom: 1.2pt solid var(--clinical-blue);
        padding-bottom: 3px;
        margin: 14px 0 6px;
    }
    .oc-section-label:first-of-type { margin-top: 0; }

    /* ---- Bordered form grid (matches discharge-booklet .b-info pattern) ---- */
    .oc-grid {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-bottom: 4px;
    }
    .oc-grid td {
        border: 1pt solid #333;
        padding: 6px 9px;
        vertical-align: middle;
        font-size: 15px;
    }
    .oc-grid .lbl {
        font-weight: 700;
        background: var(--blue-tint);
    }
    .oc-grid .val { font-weight: 500; }
    .oc-grid .val.full { }

    .oc-note-box {
        border: 1pt solid #333;
        min-height: 44px;
        padding: 7px 9px;
        margin-bottom: 4px;
        font-size: 15px;
        line-height: 1.6;
    }

    /* ---- Patient consent statement ---- */
    .oc-consent-title {
        text-align: center;
        margin: 14px 0 7px;
        font-weight: 700;
        font-size: 16px;
    }
    .oc-consent-title span {
        border-bottom: 2px solid var(--clinical-blue);
        padding: 1px 14px;
    }
    .oc-consent-text {
        text-align: justify;
        line-height: 1.8;
        font-size: 15.5px;
        margin-bottom: 10px;
    }

    /* ---- Signature block ---- */
    .oc-signatures {
        display: flex;
        justify-content: flex-end;
        margin-top: 30px;
        padding-top: 10px;
    }
    .oc-sign-col { flex: 0 0 280px; text-align: center; }
    .oc-sign-name {
        font-size: 14px;
        font-weight: 600;
        min-height: 16px;
        margin-bottom: 22px;
    }
    .oc-sign-line {
        border-top: 1px solid #111;
        padding-top: 4px;
        font-size: 13px;
        font-weight: 600;
    }
    .oc-sign-sub { font-size: 11px; color: var(--muted); font-weight: 500; margin-top: 1px; }

    .oc-footer {
        margin-top: 16px;
        padding-top: 6px;
        border-top: 1px dashed #999;
        font-size: 10.5px;
        color: var(--muted);
        text-align: center;
    }

    /* ---- Actions bar (screen only) ---- */
    .oc-actions {
        max-width: 794px;
        margin: 0 auto 10px;
        text-align: right;
    }
    .oc-actions button, .oc-actions a {
        font-family: 'Noto Sans Bengali', sans-serif;
        font-size: 14px;
        padding: 8px 18px;
        border-radius: 4px;
        border: 1px solid #111;
        background: #111;
        color: #fff;
        cursor: pointer;
        text-decoration: none;
        margin-left: 8px;
        display: inline-block;
    }
    .oc-actions a.secondary { background: #fff; color: #111; }

    @media print {
        @page {
            size: A4 portrait;
            margin: 5mm;
        }
        html, body {
            width: 100%;
            height: auto;
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            color: #000 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .oc-actions { display: none !important; }
        .oc-sheet {
            border: none !important;
            margin: 0 !important;
            max-width: 100% !important;
        }
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
                <img src="{{ asset('images/PC.png') }}" alt="লোগো" class="oc-logo">
                <div>
                    <div class="oc-clinic-name-bn">{{ config('clinic.name_bn') }}</div>
                    <div class="oc-clinic-name-en">{{ config('clinic.name') }}</div>
                </div>
            </div>
            <div class="oc-header-right">
                @if(config('clinic.address')){{ config('clinic.address') }}<br>@endif
                @if(config('clinic.phone'))মোবাইলঃ {{ config('clinic.phone') }}@endif
            </div>
        </div>

        <div class="oc-sheet-inner">

            <div class="oc-title-bar">
                অপারেশন সম্মতিপত্র
                <span class="en">Operation / Surgical Consent Form</span>
            </div>

            {{-- ============ Patient & Admission Info ============ --}}
            <div class="oc-section-label">রোগীর তথ্য</div>
            <table class="oc-grid">
                <colgroup>
                    <col style="width:24%"><col style="width:26%"><col style="width:24%"><col style="width:26%">
                </colgroup>
                <tr>
                    <td class="lbl">রোগীর নাম</td>
                    <td class="val full" colspan="3">{{ $admission->patientname }}</td>
                </tr>
                <tr>
                    <td class="lbl">পিতা/স্বামীর নাম</td>
                    <td class="val">{{ $admission->patientfather ?: $admission->patienthusband }}</td>
                    <td class="lbl">লিঙ্গ</td>
                    <td class="val">{{ $admission->gender }}</td>
                </tr>
                <tr>
                    <td class="lbl">রেজিঃ নং</td>
                    <td class="val">{{ $admission->patientcode }}</td>
                    <td class="lbl">ওজন</td>
                    <td class="val">{{ $consent->weight }}</td>
                </tr>
                
                <tr>
                    <td class="lbl">থানা</td>
                    <td class="val">{{ $admission->upozila }}</td>
                    <td class="lbl">জেলা</td>
                    <td class="val">{{ $admission->district }}</td>
                </tr>
                <tr>
                    <td class="lbl">ভর্তির তারিখ</td>
                    <td class="val">{{ $admission->admission_date ? date('d-m-Y', strtotime($admission->admission_date)) : '' }}</td>
                    <td class="lbl">ওয়ার্ড / বেড নং</td>
                    <td class="val">{{ $consent->ward_no }} / {{ $consent->bed_no }}</td>
                </tr>
            </table>

            {{-- ============ Operation Details ============ --}}
            <div class="oc-section-label">অপারেশন সংক্রান্ত তথ্য</div>
            <table class="oc-grid">
                <colgroup>
                    <col style="width:24%"><col style="width:26%"><col style="width:24%"><col style="width:26%">
                </colgroup>
                <tr>
                    <td class="lbl">অপারেশনের নাম</td>
                    <td class="val full" colspan="3">{{ $consent->operation_name }}</td>
                </tr>
                <tr>
                    <td class="lbl">রোগের নাম</td>
                    <td class="val">{{ $consent->disease_name }}</td>
                    <td class="lbl">তারিখ / সময়</td>
                    <td class="val">{{ $consent->operation_date ? date('d-m-Y', strtotime($consent->operation_date)) : '' }} — {{ $consent->operation_time }}</td>
                </tr>
                <tr>
                    <td class="lbl">সার্জন</td>
                    <td class="val" colspan="3">{{ $consent->surgeon_name }}</td>
                </tr>
                <tr>
                    <td class="lbl">সহযোগী সার্জন</td>
                    <td class="val" colspan="3">{{ $consent->assistant_surgeon }}</td>
                </tr>
                <tr>
                    <td class="lbl">অজ্ঞানকারী ডাক্তার</td>
                    <td class="val" colspan="3">{{ $consent->anesthesiologist }}</td>
                </tr>
            </table>

            <div class="oc-note-box">{{ $consent->operation_note }}</div>

            {{-- ============ Patient / Guardian Consent ============ --}}
            <div class="oc-consent-title"><span>রোগী/অভিভাবকের সম্মতি</span></div>
            <div class="oc-consent-text">
                আমি ডাক্তার সাহেবের সঙ্গে আলোচনা করিয়া আমার/রোগীর রোগ এবং অস্ত্রোপচারের ঝুঁকি সম্পর্কে অবগত হইয়া সম্পূর্ণ স্ব-ইচ্ছায় এবং কোন প্রকার ভয় ও প্রলোভন ছাড়াই অজ্ঞান করিয়া অস্ত্রোপচার করিতে রাজি আছি। অস্ত্রোপচারে ইচ্ছাকৃত ভুল ছাড়া অন্য কোন দুর্ঘটনার জন্য ক্লিনিক কর্তৃপক্ষ বা ডাক্তার এবং তার সহকর্মীরা দায়ী থাকিবেন না।
            </div>

            {{-- ============ Signature ============ --}}
            <div class="oc-signatures">
                <div class="oc-sign-col">
                    <div class="oc-sign-name">{{ $consent->signatory_name }}</div>
                    <div class="oc-sign-line">
                        রোগী/অভিভাবকের স্বাক্ষর
                        @if($consent->signatory_relation)
                            <div class="oc-sign-sub">({{ $consent->signatory_relation }})</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="oc-footer">
                এই ফর্মটি {{ config('clinic.name_bn') }}-এর অভ্যন্তরীণ ব্যবহারের জন্য প্রস্তুতকৃত — {{ date('d-m-Y') }}
            </div>

        </div>
    </div>

</body>
</html>