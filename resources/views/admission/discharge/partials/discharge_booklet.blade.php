@php
    $patientname   = $patient->patientname ?? '';
    $mobile        = $patient->mobile_no ?? '';
    $admissiondate = $admission ? date('d-m-Y', strtotime($admission->created_at)) : date('d-m-Y');
    $age           = $patient->age ?? '';
    $father        = $patient->father_name ?? '';
    $village       = $patient->village ?? $patient->address ?? '';
    $post          = $patient->post ?? '';
    $thana         = $patient->thana ?? '';
    $district      = $patient->district ?? '';
    $patientcode   = $patientcode ?? $admission->patientcode ?? '';

    $cleanVal = function ($value) {
        $value = trim((string) $value);
        return in_array($value, ['', '-- Select --'], true) ? '' : $value;
    };

    $ward        = $cleanVal($discharge->ward ?? '');
    $regNo       = $cleanVal($discharge->reg_no ?? '') ?: $patientcode;
    $doctorName  = $cleanVal($discharge->doctor_name ?? '');
    $diagnosis   = $cleanVal($discharge->diagnosis ?? '');
    $operationMethod = $cleanVal($discharge->operation_method ?? '');
    $investigations   = $cleanVal($discharge->investigations ?? '');
    $bloodGroup  = $cleanVal($discharge->blood_group ?? '');
    $drugAllergy = $cleanVal($discharge->drug_allergy ?? '');
    $notes       = $cleanVal($discharge->notes ?? '');
    $followUpDate = ($discharge && $discharge->follow_up_date)
        ? date('d-m-Y', strtotime($discharge->follow_up_date))
        : '';
    $dischargeDate = ($discharge && $discharge->discharge_date)
        ? date('d-m-Y', strtotime($discharge->discharge_date))
        : date('d-m-Y');

    $resolveMulti = function ($idString, $collection, string $nameColumn) {
        $ids = array_values(array_filter(array_map('trim', explode(',', (string) $idString)), fn ($v) => $v !== ''));
        if (empty($ids)) return '';
        $names = [];
        foreach ($ids as $id) {
            if (ctype_digit($id) && ($master = $collection->firstWhere('id', (int) $id))) {
                $names[] = $master->{$nameColumn};
            }
        }
        return implode(', ', $names);
    };

    $operationName = $discharge ? ($discharge->operation_name_id
        ? (($resolveMulti($discharge->operation_name_id, $operationNames, 'name')) ?: $discharge->operation_name)
        : $discharge->operation_name) : '';
    $surgeonText = $discharge ? ($discharge->surgeon_id
        ? (($resolveMulti($discharge->surgeon_id, $surgeons, 'doctor_name')) ?: $discharge->surgeon)
        : $discharge->surgeon) : '';
    $anesthetistText = $discharge ? ($discharge->anesthesiologist_id
        ? (($resolveMulti($discharge->anesthesiologist_id, $anesthesiologists, 'name')) ?: $discharge->anesthetist)
        : $discharge->anesthetist) : '';
    $assistant1Text = $discharge ? ($discharge->assistant_surgeon_1_id
        ? (($resolveMulti($discharge->assistant_surgeon_1_id, $assistantSurgeons, 'name')) ?: $discharge->assistant_1)
        : $discharge->assistant_1) : '';
    $assistant2Text = $discharge ? ($discharge->assistant_surgeon_2_id
        ? (($resolveMulti($discharge->assistant_surgeon_2_id, $assistantSurgeons, 'name')) ?: $discharge->assistant_2)
        : $discharge->assistant_2) : '';
    $operationName = $operationName ?? '';
    $surgeonText = $surgeonText ?? '';
    $anesthetistText = $anesthetistText ?? '';
    $assistant1Text = $assistant1Text ?? '';
    $assistant2Text = $assistant2Text ?? '';

    $logoSrc = '';
    $logoPath = public_path(config('clinic.logo', 'images/PC.png'));
    if (is_file($logoPath)) {
        $logoSrc = 'data:' . mime_content_type($logoPath) . ';base64,' . base64_encode(file_get_contents($logoPath));
    }

    $photoSrc = '';
    if (!empty($patient->photo)) {
        $photoPath = public_path(ltrim(parse_url($patient->photo, PHP_URL_PATH) ?: $patient->photo, '/'));
        if (is_file($photoPath)) {
            $photoSrc = 'data:' . mime_content_type($photoPath) . ';base64,' . base64_encode(file_get_contents($photoPath));
        }
    }

    $dotted = function ($value, $width = 30) {
        return '<span style="display:inline-block;min-width:' . $width . 'mm;border-bottom:1px solid #000;text-align:center;padding:0 2px;font-weight:600;">' . e($value ?? '') . '</span>';
    };

    $en2bn = function ($number) {
        $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        return str_replace($en, $bn, (string)$number);
    };
@endphp

{{-- ======================================================================
     SHEET 1 (prints first): LEFT = Back Cover | RIGHT = Front Cover
     ====================================================================== --}}
<table class="booklet-sheet">
    <tr>
        {{-- PAGE 4 : Back Cover — চিকিৎসা পরামর্শ ও উপদেশাবলী + ক্লিনিকের বিশেষ আকর্ষণ --}}
        <td class="booklet-page b-back">
            <div class="b-page-fill">
                <div class="b-page-top-row">
                    <div class="b-page-top">
                        <div class="b-title">চিকিৎসা পরামর্শ ও উপদেশাবলী</div>

                        <div class="b-note b-min4" data-print-field="notes">{!! $notes ? nl2br(e($notes)) : '' !!}</div>

                        <table class="b-info">
                            <tr>
                                <td class="lbl" style="width:25%;">পরবর্তী ভিজিটের<br>তারিখ</td><td class="val" style="width:75%;" data-print-field="follow_up_date">{{ $followUpDate }}</td>
                            </tr>
                        </table>

                        <div class="b-title" style="margin-top:4mm;">ক্লিনিকের বিশেষ আকর্ষণ ঃ</div>
                        <ul class="b-feature">
                            <li>&#9658; ২৪ ঘন্টা ইমার্জেন্সী সার্ভিস প্রদান।</li>
                            <li>&#9658; দিবা-রাত্রি সব সময় সিজারসহ যে কোনো অপারেশনের সু-ব্যবস্থা।</li>
                            <li>&#9658; মহিলা এম.বি.বি.এস ডাক্তার দ্বারা নরমাল ডেলিভারির সু-ব্যবস্থা।</li>
                            <li>&#9658; অন্তঃসত্ত্বা মহিলাদের জন্য চেক-আপের বিশেষ সুবিধাদি প্রদান।</li>
                        </ul>
                    </div>
                </div>
                <div class="b-page-bottom-row">
                    <div class="b-page-bottom">
                        <div class="b-ack">বিঃ দ্রঃ চিকিৎসকের পরামর্শ ব্যতীত ঔষধ সেবন বিপজ্জনক হতে পারে।</div>
                    </div>
                </div>
            </div>
        </td>

        {{-- PAGE 1 : Cover — ছাড় পত্র --}}
        <td class="booklet-page">
            <div class="b-hos-header">
                @if($logoSrc)
                    <div class="b-hos-logo"><img src="{{ $logoSrc }}"></div>
                @endif
                <div class="b-hos-details">
                    <div class="b-hos-name">{{ config('clinic.name_bn') }}</div>
                    <div class="b-hos-addr">{{ config('clinic.address') }}</div>
                    <div class="b-hos-phone">মোবাইল: {{ config('clinic.phone') }}</div>
                </div>
            </div>
            <div class="b-thinrule"></div>
            <div class="b-title">ছাড় পত্র</div>

            <table class="b-info" style="margin:0;">
                <tr>
                    <td class="lbl" colspan="3" style="text-align:right;padding-right:3mm;">ওয়ার্ড/কেবিন নং-</td>
                    <td class="val" data-print-field="ward">{{ $ward }}</td>
                </tr>
                <tr>
                    <td class="lbl" colspan="3" style="text-align:right;padding-right:3mm;">রেজিঃ নং-</td>
                    <td class="val" data-print-field="reg_no">{{ $regNo }}</td>
                </tr>
            </table>

            <div class="b-certificate">
                <div class="b-certificate-line">প্রত্যায়ন করা যাচ্ছে {!! $dotted($patientname, 40) !!}</div>
                <div class="b-certificate-line">বয়স {!! $dotted($age, 15) !!}<span class="b-field-gap"></span>পিতা/স্বামী {!! $dotted($father, 25) !!}</div>
                <div class="b-certificate-line">ঠিকানা ঃ গ্রাম {!! $dotted($village, 25) !!}<span class="b-field-gap"></span>পোষ্ট {!! $dotted($post, 16) !!}</div>
                <div class="b-certificate-line b-certificate-centered">থানা {!! $dotted($thana, 25) !!}<span class="b-field-gap"></span>জেলা {!! $dotted($district, 20) !!}</div>
                <div class="b-certificate-line">অত্র হাসপাতালে {!! $dotted($admissiondate, 18) !!} তারিখ হতে {!! $dotted($dischargeDate, 18) !!} তারিখ পর্যন্ত চিকিৎসাধীন ছিলেন।</div>
                <div class="b-certificate-line">তিনি {!! $dotted($diagnosis, 25) !!} রোগে ভুগছিলেন।</div>
                <div class="b-certificate-line">তিনি নিম্নোক্ত চিকিৎসকের অধীনে চিকিৎসাধীন ছিলেন।</div>
            </div>

            <table class="b-cover-signatures">
                <tr>
                    <td class="b-cover-seal">
                        <div class="b-sign-label">হাসপাতালের সিল</div>
                        <div class="b-sign-space"><div class="b-seal-box"></div></div>
                    </td>
                    <td class="b-cover-sign">
                        <div class="b-sign-label">কর্তৃপক্ষ চিকিৎসকের স্বাক্ষর</div>
                        <div class="b-sign-space"><div class="b-sign-box"></div></div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align:left;padding-top:1.5mm;">
                        <span style="font-size:9pt;font-weight:700;">তাং- </span>{!! $dotted($dischargeDate, 22) !!}
                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>

{{-- ======================================================================
     SHEET 2 (prints second): LEFT = অপারেশন নোট | RIGHT = প্রদত্ত চিকিৎসা পরামর্শ + ঔষধ
     ====================================================================== --}}
<table class="booklet-sheet">
    <tr>
        {{-- PAGE 2 : অপারেশন নোট — matching physical form exactly --}}
        <td class="booklet-page">
            <div class="b-page-fill">
            <div class="b-page-top-row" style="height:1px;">
            <div class="b-page-top">
            <div class="b-title">অপারেশন নোট</div>
            </div>
            </div>
            <div class="b-page-top-row">
            <div class="b-page-top" style="vertical-align:middle;">

            <table class="b-opgrid" style="margin:0;">
                {{-- অপারেশন নাম ও তাং --}}
                <tr>
                    <td class="lbl" rowspan="1">অপারেশন<br>নাম ও তাং</td>
                    <td class="val" data-print-field="operation_name">{{ $operationName }}</td>
                    <td class="val-date" data-print-field="operation_date">{{ ($discharge && $discharge->operation_date) ? date('d-m-Y', strtotime($discharge->operation_date)) : '' }}</td>
                </tr>
                {{-- অপারেশন পদ্ধতি ও প্রাপ্ত তথ্যাবলী --}}
                <tr>
                    <td class="lbl">অপারেশন পদ্ধতি<br>ও প্রাপ্ত তথ্যাবলী<br>নাম ও তাং</td>
                    <td class="val-wide" colspan="2" style="height:120pt;" data-print-field="operation_method">{{ $operationMethod }}</td>
                </tr>
                {{-- সার্জন --}}
                <tr>
                    <td class="lbl">সার্জন</td>
                    <td class="val" data-print-field="surgeon" colspan="2">{{ $surgeonText }}</td>
                </tr>
                {{-- এ্যানেসথেটিষ্ট --}}
                <tr>
                    <td class="lbl">এ্যানেসথেটিষ্ট</td>
                    <td class="val" data-print-field="anesthetist" colspan="2">{{ $anesthetistText }}</td>
                </tr>
                {{-- এ্যাসিসট্যান্ট ১ --}}
                <tr>
                    <td class="lbl">এ্যাসিসট্যান্ট</td>
                    <td class="val" data-print-field="assistant_1" colspan="2">{{ $assistant1Text }}</td>
                </tr>
                {{-- এ্যাসিসট্যান্ট ২ --}}
                @if(!empty($assistant2Text))
                <tr>
                    <td class="lbl">এ্যাসিসট্যান্ট ২</td>
                    <td class="val" data-print-field="assistant_2" colspan="2">{{ $assistant2Text }}</td>
                </tr>
                @endif
            </table>

            </div>
            </div>
            </div>
        </td>

        {{-- PAGE 3 : প্রদত্ত চিকিৎসা পরামর্শ + ঔষধ --}}
        <td class="booklet-page">
            <div class="b-page-fill">
            <div class="b-page-top-row" style="height:1px;">
            <div class="b-page-top">
            <div class="b-title">প্রদত্ত চিকিৎসা পরামর্শ</div>
            </div>
            </div>
            <div class="b-page-top-row">
            <div class="b-page-top" style="height:100%;">

            {{-- ঔষধ তালিকা — prescription style list inside bordered box --}}
            <div class="b-value" style="height:100%;min-height:150pt;padding:5pt 7pt;margin:0;" id="print-medicine-box">
                <div style="font-weight:700;font-size:13pt;margin-bottom:4pt;text-decoration:underline;text-align:center;">ঔষধ ঃ</div>
                <div id="print-medicine-list" style="font-size:12pt;line-height:1.6;">
                @if(isset($savedMedicines) && $savedMedicines->count())
                    @foreach($savedMedicines as $medIndex => $med)
                        <div style="margin-bottom:2pt;font-size:12pt;">
                            <span style="font-weight:700;">{{ $en2bn($medIndex + 1) }}.</span>
                            <span style="font-weight:600;">{{ $med->medicine_name ?? '' }}</span>
                            @if(!empty($med->strength)) ({{ $med->strength }})@endif
                            @if(!empty($med->morning) || !empty($med->noon) || !empty($med->night))
                                &mdash; {{ $en2bn($med->morning ?? '0') }}+{{ $en2bn($med->noon ?? '0') }}+{{ $en2bn($med->night ?? '0') }}
                            @endif
                            @if(!empty($med->days)) &times; {{ $en2bn($med->days) }} দিন@endif
                            @if(!empty($med->timing)) ({{ $med->timing }})@endif
                        </div>
                    @endforeach
                @endif
                </div>
            </div>

            {{-- রক্ত গ্রুপ ও ড্রাগ এ্যালার্জী at bottom --}}
            </div>
            </div>
            <div class="b-page-bottom-row">
            <div class="b-page-bottom">

            <div style="font-size:13pt;line-height:1.8;text-align:center;">                <span>রক্ত গ্রুপ {!! $dotted($bloodGroup, 22) !!}</span>
                <span style="margin-left:6mm;">ড্রাগ এ্যালার্জী {!! $dotted($drugAllergy, 22) !!}</span>
            </div>

            <div class="b-sign-block">
                <div class="b-sign-line">চিকিৎসকের স্বাক্ষর</div>
                <div class="b-sign-box"></div>
            </div>

            </div>
            </div>
            </div>
        </td>
    </tr>
</table>
