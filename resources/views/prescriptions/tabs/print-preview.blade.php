@php
    $clinicName  = config('app.name', 'Professor Clinic');
    $clinicAddr  = $clinicAddr ?? 'Shah Amanat (R.) Road, G.E.C. Circle, Chattagram';
    $clinicPhone = $clinicPhone ?? 'Phone: 880-18-19-433633';
    $stageLabel  = $stageLabel ?? '';
    $adviceText  = '';

    // Build doctor display
    $docLine = '—';
    if (!empty($rxDoctors) && ($rxDoctors instanceof \Illuminate\Support\Collection ? $rxDoctors->count() : count($rxDoctors))) {
        $docLine = collect($rxDoctors)->map(function($d){
            $name = $d->doctor_name ?? $d->name ?? '';
            $role = $d->role ?? '';
            return trim($name . ($role ? " ({$role})" : ''));
        })->filter()->values()->join(', ');
    } elseif (!empty($rx->doctor_name)) {
        $docLine = $rx->doctor_name;
    } elseif (!empty($rx->surgeon_name)) {
        $docLine = $rx->surgeon_name;
    }

    // Advice: from rx object, or from advice collection
    if (!empty($rx->advice)) {
        $adviceText = $rx->advice;
    } elseif (!empty($rx->instructions)) {
        $adviceText = $rx->instructions;
    } elseif (isset($advices) && ($advices instanceof \Illuminate\Support\Collection ? $advices->count() : count($advices))) {
        $adviceText = collect($advices)->pluck('advice')->filter()->implode("\n");
    }

    // Prescription ID / display ID
    $displayId = $rx->prescription_no ?? $rx->id ?? ($rx->admission_no ?? '—');
    $displayDate = $rx->prescribed_on ?? $rx->display_date ?? ($rx->surgery_date ?? $rx->created_at ?? now()->toDateString());

    // Source badge color map
    $badgeMap = [
        'regular'       => ['label' => 'Regular RX',       'color' => 'primary'],
        'on_admission'  => ['label' => 'On Admission',     'color' => 'primary'],
        'pre_surgery'   => ['label' => 'Pre-Surgery',      'color' => 'warning'],
        'post_surgery'  => ['label' => 'Post-Surgery',     'color' => 'info'],
        'round'         => ['label' => 'Round',             'color' => 'secondary'],
        'fresh'         => ['label' => 'Fresh Rx',          'color' => 'success'],
        'discharge'     => ['label' => 'Discharge',         'color' => 'danger'],
        'surgery'       => ['label' => 'Surgery',           'color' => 'dark'],
    ];
    $badgeInfo = $badgeMap[$stageLabel] ?? ['label' => $stageLabel ?: 'Prescription', 'color' => 'dark'];
@endphp
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
    @font-face {
        font-family: 'notobengali';
        font-weight: normal;
        font-style: normal;
        src: url("{{ asset('fonts/NotoSansBengali-Regular.ttf') }}") format('truetype');
    }
    @font-face {
        font-family: 'notobengali';
        font-weight: bold;
        font-style: normal;
        src: url("{{ asset('fonts/NotoSansBengali-Bold.ttf') }}") format('truetype');
    }
    body, *{
        font-family: 'notobengali', 'Helvetica', 'Arial', sans-serif !important;
    }

    :root{
        --ink:#0f172a;
        --muted:#6b7280;
        --line:#e5e7eb;
        --soft:#f8fafc;
        --brand:#0ea5e9;
    }

    .rx-wrap{
        max-width: 920px;
        margin: 0 auto;
        padding: 12px;
        color: var(--ink);
    }

    #printableArea{
        background:#fff;
        padding: 8px;
    }

    .print-header{
        display:flex;justify-content:space-between;align-items:flex-start;gap:12px;
        padding-bottom:10px;border-bottom:2px solid var(--line);margin-bottom:12px;
    }
    .clinic-left{display:flex;gap:10px;align-items:center;}
    .clinic-logo{
        width:54px;height:54px;border-radius:10px;background:var(--soft);
        border:1px solid var(--line);display:flex;align-items:center;justify-content:center;
        font-weight:700;color:var(--brand);font-size:20px;
    }
    .clinic-title h2{margin:0;font-size:20px;line-height:1.2;}
    .clinic-title .sub{font-size:12px;color:var(--muted);}

    .source-badge{
        display:inline-block;
        background:#1e293b;color:#fff;
        font-size:11px;font-weight:700;
        padding:3px 10px;border-radius:4px;
        letter-spacing:.3px;
    }

    .patient-info-bar{
        background-color:#fce4ec;
        padding:10px 16px;
        border-radius:8px;
        margin-bottom:12px;
        font-size:13px;
    }
    .patient-info-bar .info-label{font-weight:bold;color:#555;}
    .patient-info-bar .info-value{border-bottom:1px dotted #000;display:inline-block;min-width:120px;margin-right:16px;}

    .box{
        border:1px solid var(--line);border-radius:12px;padding:10px 12px;margin-bottom:10px;background:#fff;
    }
    .box h3{
        margin:0 0 8px 0;font-size:14px;padding-bottom:6px;border-bottom:1px solid var(--line);
    }
    .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:10px;}

    table.rx{width:100%;border-collapse:collapse;font-size:12.5px;}
    table.rx th, table.rx td{border:1px solid var(--line);padding:5px 7px;vertical-align:top;}
    table.rx th{background:var(--soft);text-align:left;font-weight:700;}
    table.rx td{font-size:12px;}

    .sign{display:flex;justify-content:space-between;gap:10px;margin-top:16px;}
    .sign .line{
        width:48%;border-top:1px solid #9ca3af;text-align:center;padding-top:4px;font-size:12px;color:var(--muted);
    }

    .print-footer{
        border-top:1px dashed var(--line);
        margin-top:12px;padding-top:8px;font-size:11px;color:var(--muted);
        text-align:center;
    }

    .actions{
        display:flex;gap:.5rem;justify-content:center;margin-top:1rem;
    }
    .btn{
        padding:.5rem 1.2rem;border-radius:.6rem;border:1px solid #cfe1f4;
        background:var(--brand);color:#fff;cursor:pointer;display:inline-block;font-size:13px;font-weight:600;
        text-decoration:none;
    }
    .btn:hover{opacity:.9;}

    @page{ size:A4 portrait; margin:16mm 12mm; }

    @media print{
        body{ background:#fff !important;margin:0;padding:0; }
        body *{ visibility:hidden !important; }
        #printableArea, #printableArea *{ visibility:visible !important; }
        #printableArea{ position:absolute; left:0; top:0; right:0; width:100%; padding:0; margin:0; }
        .source-badge{ display:none !important; }
        .actions{ display:none !important; }
        .box{ page-break-inside: avoid; }
    }
</style>

<div class="rx-wrap">

<div id="printableArea">

    {{-- Source Badge (screen only) --}}
    <div class="source-badge" style="margin-bottom:8px;">
        &#9889; {{ $badgeInfo['label'] }}
    </div>

    {{-- Header --}}
    <div class="print-header">
        <div class="clinic-left">
            <div class="clinic-logo">Rx</div>
            <div class="clinic-title">
                <h2>{{ $clinicName }}</h2>
                <div class="sub">{{ $clinicAddr }}</div>
                <div class="sub">{{ $clinicPhone }}</div>
            </div>
        </div>
        <div style="font-size:12px;color:var(--muted);text-align:right;">
            <div><b>Prescription No:</b> {{ $displayId }}</div>
            <div><b>Date:</b> {{ \Carbon\Carbon::parse($displayDate)->format('d-m-Y') }}</div>
            <div><b>Patient ID:</b> {{ $patient->patientcode ?? $patient->id ?? '—' }}</div>
        </div>
    </div>

    {{-- Doctor + Patient Info --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
        <div style="border:1px solid var(--line);border-radius:10px;padding:8px 10px;background:#fff;">
            <b>Doctor:</b> {{ $docLine }}
            @if(!empty($rx->surgeon_name))
                <div><b>Surgeon:</b> {{ $rx->surgeon_name }}</div>
            @endif
        </div>
        <div style="border:1px solid var(--line);border-radius:10px;padding:8px 10px;background:#fff;">
            <div><b>Name:</b> {{ $patient->patientname ?? $patient->patient_name ?? '—' }}</div>
            <div><b>Age/Gender:</b> {{ $patient->age ?? '—' }} / {{ $patient->gender ?? '—' }}</div>
            <div><b>Mobile:</b> {{ $patient->mobile_no ?? $patient->patient_phone ?? '—' }}</div>
        </div>
    </div>

    {{-- Complaints + Diagnosis --}}
    @if(($complaints ?? collect())->count() || ($diagnoses ?? collect())->count() || !empty($adviceText))
    <div class="box">
        <h3>Complaints &amp; Diagnosis</h3>
        <div class="grid-2">
            <div>
                <b>Complaints:</b>
                @if(($complaints ?? collect())->count())
                <ul style="margin:4px 0 0;padding-left:18px;">
                    @foreach($complaints as $c)
                        <li>{{ $c->complaint ?? $c->name ?? $c->complain ?? '' }}
                            @if(!empty($c->note)) — <em>{{ $c->note }}</em>@endif
                        </li>
                    @endforeach
                </ul>
                @else
                <div style="color:var(--muted);">—</div>
                @endif
            </div>
            <div>
                <b>Diagnosis:</b>
                @if(($diagnoses ?? collect())->count())
                <ul style="margin:4px 0 0;padding-left:18px;">
                    @foreach($diagnoses as $d)
                        <li>{{ $d->name ?? $d->diagnosis_name ?? '' }}
                            @if(!empty($d->note) || !empty($d->notes)) — <em>{{ $d->note ?? $d->notes }}</em>@endif
                        </li>
                    @endforeach
                </ul>
                @else
                <div style="color:var(--muted);">—</div>
                @endif
            </div>
        </div>

        @if(!empty($adviceText))
        <div style="margin-top:8px;">
            <b>Advice:</b><br>
            <div style="white-space:pre-line;color:#444;">{{ $adviceText }}</div>
        </div>
        @endif
    </div>
    @endif

    {{-- Investigations --}}
    @if(($investigations ?? collect())->count())
    <div class="box">
        <h3>Investigations</h3>
        <table class="rx">
            <thead>
                <tr>
                    <th style="width:40%;">Test</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>
                @foreach($investigations as $i)
                <tr>
                    <td>{{ $i->name ?? $i->investigation_name ?? $i->test ?? '—' }}</td>
                    <td>{{ $i->note ?? $i->notes ?? $i->remarks ?? $i->comment ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Medicines --}}
    <div class="box">
        <h3>Medicines</h3>
        @if(($medicines ?? collect())->count())
        <table class="rx">
            <thead>
                <tr>
                    <th style="width:35px;">Sl</th>
                    <th>Medicine</th>
                    <th style="width:55px;">Dose</th>
                    <th style="width:70px;">Duration</th>
                    <th>Instruction</th>
                </tr>
            </thead>
            <tbody>
                @foreach($medicines as $i => $m)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ $m->name ?? $m->medicine_name ?? '—' }}</strong>
                        @if(!empty($m->strength))
                            <br><small style="color:var(--muted);">{{ $m->strength }}</small>
                        @endif
                    </td>
                    <td>{{ $m->dose ?? $m->dosage ?? $m->dose_morning ?? '' }}</td>
                    <td>{{ $m->duration ?? $m->duration_days ?? '—' }}</td>
                    <td>
                        {{ $m->instruction ?? $m->instructions ?? $m->timing ?? $m->frequency ?? $m->note ?? '' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="color:var(--muted);font-style:italic;">No medicines prescribed.</div>
        @endif
    </div>

    {{-- Signature --}}
    <div class="sign">
        <div class="line">Patient / Guardian</div>
        <div class="line">Doctor's Signature</div>
    </div>

    {{-- Footer --}}
    <div class="print-footer">
        This is a computer-generated prescription.<br>
        Contact: {{ $clinicPhone }}
    </div>

</div>{{-- /printableArea --}}

{{-- Print Button (screen only) --}}
<div class="actions">
    <button type="button" class="btn" onclick="window.print();return false;">
        <i class="fas fa-print"></i> Print Prescription
    </button>
</div>

</div>{{-- /rx-wrap --}}
