{{--
    CANONICAL PRESCRIPTION SHEET — shared markup for every prescription print.

    Renders the full A4 sheet: header, C/C - O/E - Dx - Inv sidebar, patient
    grid, Rx medicine table, extra note blocks, signature and footer.
    All labels are English.

    Variables (every one optional):
        $rxPatientCode       string
        $rxPatientName       string
        $rxPatientMobile     string
        $rxPatientAge        string  shown instead of mobile when mobile is empty
        $rxDate              string  already formatted; defaults to today d-m-Y
        $rxCC                string  newline separated
        $rxOE                string  newline separated
        $rxDx                string  newline separated, block hidden when empty
        $rxInv               string  newline separated
        $rxMedicineRows      string  pre-rendered <tr> html (raw)
        $rxMedicines         iterable rows built from these when $rxMedicineRows is unset
        $rxMinRows           int     pad to this many rows, default 8
        $rxNotes             array   [['title' => 'Advice', 'body' => "line\nline"], ...]
        $rxSignatureLabel    string  default "Doctor's Signature"
        $rxFooterLeft        string
        $rxFooterRight       string
--}}
@php
    $rxHelpers = include resource_path('views/partials/rx_helpers.php');

    $rxPatientCode    = trim((string) ($rxPatientCode ?? ''));
    $rxPatientName    = trim((string) ($rxPatientName ?? ''));
    $rxPatientMobile  = trim((string) ($rxPatientMobile ?? ''));
    $rxPatientAge     = trim((string) ($rxPatientAge ?? ''));
    $rxDate           = trim((string) ($rxDate ?? '')) ?: now()->format('d-m-Y');
    $rxCC             = (string) ($rxCC ?? '');

    // ── ALL TIME O/E format: Pulse, BP, Temp, Weight ──
    $rxOEInput = trim((string) ($rxOE ?? ''));
    $oeDefaults = ['Pulse' => '', 'BP' => '', 'Temp' => '', 'Weight' => ''];
    if ($rxOEInput !== '') {
        foreach (explode("\n", $rxOEInput) as $line) {
            $line = trim($line);
            if ($line === '') continue;
            if (str_contains($line, ':')) {
                [$k, $v] = explode(':', $line, 2);
                $k = trim($k); $v = trim($v);
                $matched = false;
                foreach (array_keys($oeDefaults) as $defK) {
                    if (strcasecmp($k, $defK) === 0) {
                        $oeDefaults[$defK] = $v;
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) $oeDefaults[$k] = $v;
            } else {
                $oeDefaults[$line] = '';
            }
        }
    }
    $oeRenderLines = [];
    foreach ($oeDefaults as $k => $v) {
        $oeRenderLines[] = $k . ': ' . $v;
    }
    $rxOERender = implode("\n", $oeRenderLines);

    $rxDx             = (string) ($rxDx ?? '');
    $rxInv            = (string) ($rxInv ?? '');
    $rxMinRows        = (int) ($rxMinRows ?? 8);
    $rxNotes          = $rxNotes ?? [];
    $rxFooterLeft     = $rxFooterLeft ?? 'বিধি ধারা ............................................';
    $rxFooterRight    = $rxFooterRight ?? '............... দিন/মাস পর পুনর্বার দেখা করুন।';

    // Third field of the patient grid: mobile when known, otherwise age.
    $rxThirdLabel = $rxPatientMobile !== '' || $rxPatientAge === '' ? 'Mobile' : 'Age';
    $rxThirdValue = $rxPatientMobile !== '' || $rxPatientAge === '' ? $rxPatientMobile : $rxPatientAge;

    $rxRowsHtml = $rxMedicineRows ?? $rxHelpers['rows'](collect($rxMedicines ?? []), $rxMinRows);
@endphp
<main class="rx-page">
    <section class="rx-info">
        <aside class="rx-cc">
            <div class="rx-cc-title">C/C:</div>
            <div class="rx-cc-note">{!! nl2br(e($rxCC)) !!}</div>

            <div class="rx-cc-title rx-cc-block">O/E:</div>
            <div class="rx-cc-note">{!! nl2br(e($rxOERender)) !!}</div>

            @if(trim($rxDx) !== '')
                <div class="rx-cc-title rx-cc-block">Dx:</div>
                <div class="rx-cc-note">{!! nl2br(e($rxDx)) !!}</div>
            @endif

            <div class="rx-cc-title rx-cc-block">Inv:</div>
            <div class="rx-cc-note">{!! nl2br(e($rxInv)) !!}</div>
        </aside>
        <div>
            <div class="rx-patient-grid">
                <div class="rx-field"><b>রোগীর কোড:</b> {!! $rxPatientCode !== '' ? e($rxPatientCode) : '&nbsp;' !!}</div>
                <div class="rx-field"><b>রোগীর নাম:</b> {!! $rxPatientName !== '' ? e($rxPatientName) : '&nbsp;' !!}</div>
                <div class="rx-field"><b>{{ $rxThirdLabel === 'Mobile' ? 'মোবাইল' : 'বয়স' }}:</b> {!! $rxThirdValue !== '' ? e($rxThirdValue) : '&nbsp;' !!}</div>
                <div class="rx-field"><b>তারিখ:</b> {{ $rxDate }}</div>
            </div>

            <table class="rx-med-table">
                <colgroup>
                    <col style="width:30%">
                    <col style="width:8.5%"><col style="width:8.5%"><col style="width:8.5%">
                    <col style="width:8%"><col style="width:8%">
                    <col style="width:10.5%">
                    <col style="width:18%">
                </colgroup>
                <thead>
                    <tr>
                        <th rowspan="2" class="rx-med-head">ঔষধের নাম</th>
                        <th colspan="3">কখন খাবেন?</th>
                        <th colspan="2">আহারের</th>
                        <th rowspan="2">কতদিন</th>
                        <th rowspan="2">নির্দেশনা</th>
                    </tr>
                    <tr>
                        <th>সকাল</th>
                        <th>দুপুর</th>
                        <th>রাত</th>
                        <th>আগে</th>
                        <th>পরে</th>
                    </tr>
                </thead>
                <tbody>{!! $rxRowsHtml !!}</tbody>
            </table>

            @foreach($rxNotes as $rxNote)
                @php
                    $rxNoteBody  = trim((string) ($rxNote['body'] ?? ''));
                    $rxNoteItems = $rxNote['items'] ?? [];
                @endphp
                @if($rxNoteBody !== '' || count($rxNoteItems))
                    <div class="rx-note-block">
                        <b>{{ $rxNote['title'] ?? '' }}:</b>
                        @if($rxNoteBody !== '')
                            <br>{!! nl2br(e($rxNoteBody)) !!}
                        @endif
                        @if(count($rxNoteItems))
                            <ul class="rx-note-list">
                                @foreach($rxNoteItems as $rxNoteItem)
                                    <li>{{ $rxNoteItem }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    </section>
</main>
