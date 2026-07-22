{{-- resources/views/prescriptions/search.blade.php --}}
@extends('adminlte::page')

@section('title','Search Prescription')

@section('content_header')

<style>
  /* ── Clinical Dashboard Design System ── */
  @import url('https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap');

  body, .content-wrapper, .wrapper {
    background: #f5f6f9 !important;
    font-family: 'Hanken Grotesk', sans-serif !important;
  }

  :root{
    --rxd-blue:       #0f52ba;
    --rxd-blue-tint:  #eaf1fb;
    --rxd-border:     #e3e5eb;
    --rxd-surface:    #ffffff;
    --rxd-text:       #1a1a2e;
    --rxd-muted:      #7b818f;
    --rxd-radius:     6px;
  }

  /* Neutralize AdminLTE's default header chrome so our app bar owns the top */
  .content-header{ background:transparent !important; padding:0 !important; margin:-10px -15px 16px !important; }
  .content-header h1{ display:none !important; }
  .content-wrapper{ padding-bottom: 30px !important; }

  /* ══════════════ APP BAR: patient summary + search ══════════════ */
  .rxd-appbar{
    display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;
    background:var(--rxd-surface); border:.5px solid var(--rxd-border); border-radius:var(--rxd-radius);
    padding:14px 18px;
  }
  .rxd-profile{ display:flex; align-items:center; gap:12px; min-width:0; }
  .rxd-avatar{
    width:42px; height:42px; border-radius:50%; flex:0 0 42px;
    background:var(--rxd-blue); color:#fff; font-weight:700; font-size:14px;
    display:flex; align-items:center; justify-content:center; letter-spacing:.3px;
  }
  .rxd-avatar.is-empty{ background:#eceef2; color:#9aa0ab; }
  .rxd-profile-text{ min-width:0; }
  .rxd-profile-name{
    font-size:15px; font-weight:700; color:var(--rxd-text);
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:280px;
  }
  .rxd-profile-name .rxd-pid{ font-weight:500; color:var(--rxd-muted); font-size:13px; }
  .rxd-profile-sub{ font-size:12px; color:var(--rxd-muted); margin-top:1px; }

  .rxd-search-form{ display:flex; align-items:center; gap:8px; }
  .rxd-search-wrap{
    display:flex; align-items:center; gap:6px;
    background:#f7f8fb; border:1px solid var(--rxd-border); border-radius:999px;
    padding:6px 6px 6px 14px;
  }
  .rxd-search-wrap i{ color:var(--rxd-muted); font-size:13px; }
  .rxd-search-wrap select{
    border:none; background:transparent; outline:none; font-size:13px; color:var(--rxd-text);
    font-family:'Hanken Grotesk',sans-serif; padding:6px 2px; min-width:180px;
    appearance:none; -webkit-appearance:none; cursor:pointer;
  }
  .rxd-search-submit{
    width:30px; height:30px; border-radius:50%; border:none; background:var(--rxd-blue); color:#fff;
    display:flex; align-items:center; justify-content:center; cursor:pointer; flex:0 0 30px; font-size:12px;
    transition:opacity .15s;
  }
  .rxd-search-submit:hover{ opacity:.88; }

  @media (max-width:576px){
    .rxd-appbar{ flex-direction:column; align-items:stretch; }
    .rxd-search-form{ justify-content:stretch; }
    .rxd-search-wrap{ flex:1; }
    .rxd-search-wrap select{ flex:1; min-width:0; }
  }
</style>

<div class="rxd-appbar">
  {{-- Patient profile summary --}}
  <div class="rxd-profile">
    @if($selectedPatient)
      @php
        $parts = preg_split('/\s+/', trim($selectedPatient->patientname ?? ''));
        $initials = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
      @endphp
      <div class="rxd-avatar">{{ $initials ?: '?' }}</div>
      <div class="rxd-profile-text">
        <div class="rxd-profile-name">
          {{ $selectedPatient->patientname }}
          <span class="rxd-pid">({{ $selectedPatient->patientcode ?? $selectedPatient->id }})</span>
        </div>
        <div class="rxd-profile-sub">{{ $selectedPatient->mobile_no ?? $selectedPatient->mobileno ?? '—' }}</div>
      </div>
    @else
      <div class="rxd-avatar is-empty"><i class="fas fa-user"></i></div>
      <div class="rxd-profile-text">
        <div class="rxd-profile-name">No patient selected</div>
        <div class="rxd-profile-sub">Choose a patient to view prescriptions</div>
      </div>
    @endif
  </div>

  {{-- Patient search --}}
  <form method="GET" action="{{ route('rx.search') }}" class="rxd-search-form">
    <div class="rxd-search-wrap">
      <i class="fas fa-search"></i>
      <select name="patient">
        <option value="">Select patient…</option>
        @foreach($patients as $p)
          <option value="{{ $p->id }}" {{ (int)$patientId === (int)$p->id ? 'selected' : '' }}>
            {{ $p->patientname }} ({{ $p->mobile_no ?? $p->mobileno }})
          </option>
        @endforeach
      </select>
    </div>
    <button type="submit" class="rxd-search-submit" title="Search">
      <i class="fas fa-arrow-right"></i>
    </button>
  </form>
</div>

@stop

@section('content')

<style>
  /* ══════════════ TOOLBAR ══════════════ */
  .rxd-toolbar{
    display:flex; align-items:center; justify-content:space-between;
    margin-bottom:10px; padding:0 2px;
  }
  .rxd-toolbar-title{ font-size:14px; font-weight:700; color:var(--rxd-text); }
  .rxd-count-badge{
    background:var(--rxd-blue-tint); color:var(--rxd-blue);
    font-size:11px; font-weight:700; padding:3px 11px; border-radius:20px;
  }

  /* ══════════════ TABLE ══════════════ */
  .rxd-table-card{
    background:var(--rxd-surface); border:.5px solid var(--rxd-border); border-radius:var(--rxd-radius);
    overflow:hidden;
  }
  .rxd-table-scroll{
    max-height: 64vh;
    overflow-y: auto;
    overflow-x: auto;
  }
  table.rxd-table{
    width:100%; min-width:760px; border-collapse:collapse; font-size:13px;
  }
  table.rxd-table thead th{
    position:sticky; top:0; z-index:2;
    background:var(--rxd-blue-tint); color:var(--rxd-blue);
    font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px;
    text-align:left; padding:10px 14px; border-bottom:1px solid var(--rxd-border);
    white-space:nowrap;
  }
  table.rxd-table thead th.rxd-col-actions{ text-align:center; }
  table.rxd-table tbody td{
    padding:9px 14px; border-bottom:1px solid var(--rxd-border);
    color:var(--rxd-text); vertical-align:middle; white-space:nowrap;
  }
  table.rxd-table tbody tr:last-child td{ border-bottom:none; }
  table.rxd-table tbody tr:hover{ background:#fafbfd; }

  .rxd-id-cell{
    font-family:'JetBrains Mono', monospace; font-size:11.5px; color:var(--rxd-muted);
    max-width:170px; overflow:hidden; text-overflow:ellipsis;
  }
  .rxd-category-cell{ color:var(--rxd-muted); font-size:12.5px; font-weight:500; }

  /* Status badges */
  .rxd-badge{
    font-size:10px; font-weight:700; padding:3px 10px; border-radius:20px;
    text-transform:uppercase; letter-spacing:.4px; display:inline-flex; align-items:center;
  }
  .badge-on-admission  { background:#f3e8ff; color:#6b21a8; }
  .badge-pre-surgery   { background:#fff7e8; color:#92400e; }
  .badge-post-surgery  { background:#e8fdf4; color:#065f46; }
  .badge-round         { background:#e8f4fd; color:#0f52ba; }
  .badge-fresh         { background:#e8fdf4; color:#065f46; }
  .badge-discharge     { background:#fde8e8; color:#b91c1c; }
  .badge-prescription  { background:#e8f4fd; color:#0f52ba; }
  .badge-surgery       { background:#fff7e8; color:#92400e; }
  .badge-default       { background:#f1f0f0; color:#4b5563; }

  /* Actions */
  .rxd-col-actions, table.rxd-table tbody td.rxd-actions-cell{ text-align:center; }
  .rxd-icon-btn{
    width:30px; height:30px; border-radius:50%; border:none;
    background:var(--rxd-blue); color:#fff; font-size:12px; cursor:pointer;
    display:inline-flex; align-items:center; justify-content:center;
    transition:opacity .15s;
  }
  .rxd-icon-btn:hover{ opacity:.85; }
  .rxd-icon-btn[disabled]{ background:#e7e9ee; color:#aab0ba; cursor:default; }

  /* Empty state */
  .rxd-empty-row td{ padding:34px 18px !important; text-align:center; }
  .rxd-empty-row i{ font-size:30px; color:#d9d9e2; display:block; margin-bottom:8px; }
  .rxd-empty-row span{ color:var(--rxd-muted); font-size:13px; }

  /* ══════════════ PAGINATION ══════════════ */
  .rxd-pagination{
    display:flex; align-items:center; justify-content:center; gap:5px;
    padding:12px 0 2px;
  }
  .rxd-page-btn{
    width:28px; height:28px; border-radius:var(--rxd-radius);
    border:.5px solid var(--rxd-border); background:var(--rxd-surface); color:var(--rxd-muted);
    font-size:12px; font-weight:500; display:flex; align-items:center; justify-content:center;
    text-decoration:none;
  }
  .rxd-page-btn.active{ background:var(--rxd-blue); color:#fff; border-color:var(--rxd-blue); }

  @media (max-width:576px){
    .rxd-table-scroll{ max-height: 70vh; }
  }
</style>

@if($selectedPatient)

  @php $count = $prescriptions->count(); @endphp

  <div class="rxd-toolbar">
    <span class="rxd-toolbar-title">Prescriptions</span>
    <span class="rxd-count-badge">{{ $count }} {{ Str::plural('Record', $count) }}</span>
  </div>

  <div class="rxd-table-card">
    <div class="rxd-table-scroll">
      <table class="rxd-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Prescription ID</th>
            <th>Category</th>
            <th>Doctor</th>
            <th>Status</th>
            <th class="rxd-col-actions">Actions</th>
          </tr>
        </thead>
        <tbody>
          @if($prescriptions->isEmpty())
            <tr class="rxd-empty-row">
              <td colspan="6">
                <i class="fas fa-file-medical"></i>
                <span>No prescriptions found for this patient.</span>
              </td>
            </tr>
          @else
            @foreach($prescriptions as $rx)
              @php
                /* ── Badge class + label logic (unchanged) ── */
                if ($rx->source === 'prescription') {
                    $badgeClass = 'badge-prescription';
                    $badgeLabel = 'Prescription';
                } elseif ($rx->source === 'surgery') {
                    $badgeClass = 'badge-surgery';
                    $badgeLabel = $rx->label ?? 'Surgery';
                } elseif ($rx->source === 'admission') {
                    $stageMap = [
                        'on_admission' => ['badge-on-admission',  'On Admission'],
                        'pre_surgery'  => ['badge-pre-surgery',   'Pre-Surgery'],
                        'post_surgery' => ['badge-post-surgery',  'Post-Surgery'],
                        'round'        => ['badge-round',         'Round'],
                        'fresh'        => ['badge-fresh',         'Fresh'],
                        'discharge'    => ['badge-discharge',     'Discharge'],
                    ];
                    [$badgeClass, $badgeLabel] = $stageMap[$rx->stage ?? ''] ?? ['badge-default', $rx->label ?? 'Admission'];
                } else {
                    $badgeClass = 'badge-default';
                    $badgeLabel = $rx->label ?? 'Admission';
                }

                /* ── Category label (display-only, derived from existing source field) ── */
                $categoryMap = [
                    'prescription' => 'Prescription',
                    'admission'    => 'Admission',
                    'surgery'      => 'Surgery',
                ];
                $categoryLabel = $categoryMap[$rx->source ?? ''] ?? 'Other';
              @endphp

              <tr>
                <td>{{ \Carbon\Carbon::parse($rx->display_date ?? $rx->prescribed_on ?? $rx->created_at)->format('d-m-Y') }}</td>
                <td class="rxd-id-cell" title="RX-{{ $rx->id }}">RX-{{ $rx->id }}</td>
                <td class="rxd-category-cell">{{ $categoryLabel }}</td>
                <td>
                  @if($rx->source === 'prescription')
                    {{ $rx->doctor_name ?? '—' }}
                  @else
                    {{ $rx->label ?? '—' }}
                  @endif
                </td>
                <td><span class="rxd-badge {{ $badgeClass }}">{{ $badgeLabel }}</span></td>
                <td class="rxd-actions-cell">
                  @if($rx->source === 'prescription')
                    <button type="button"
                            class="rxd-icon-btn btn-print-rx"
                            data-id="{{ $rx->id }}"
                            title="Print Prescription">
                      <i class="fas fa-print"></i>
                    </button>
                  @elseif($rx->source === 'admission')
                    <button type="button"
                            class="rxd-icon-btn btn-print-adm"
                            data-admissionid="{{ $rx->admissionid }}"
                            data-stage="{{ $rx->stage }}"
                            data-table="{{ $rx->table_name }}"
                            data-label="{{ $rx->label }}"
                            data-patientcode="{{ $selectedPatient->patientcode ?? '' }}"
                            title="Print Prescription">
                      <i class="fas fa-print"></i>
                    </button>
                  @else
                    <button type="button" class="rxd-icon-btn" disabled title="Not printable">
                      <i class="fas fa-print"></i>
                    </button>
                  @endif
                </td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>
  </div>

  {{-- Pagination --}}
  @if($prescriptions instanceof \Illuminate\Pagination\LengthAwarePaginator && $prescriptions->lastPage() > 1)
    <div class="rxd-pagination">
      <a href="{{ $prescriptions->previousPageUrl() }}" class="rxd-page-btn" style="{{ $prescriptions->onFirstPage() ? 'opacity:.4;pointer-events:none;' : '' }}">
        <i class="fas fa-chevron-left" style="font-size:11px;"></i>
      </a>
      @for($i = 1; $i <= $prescriptions->lastPage(); $i++)
        <a href="{{ $prescriptions->url($i) }}" class="rxd-page-btn {{ $prescriptions->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
      @endfor
      <a href="{{ $prescriptions->nextPageUrl() }}" class="rxd-page-btn" style="{{ !$prescriptions->hasMorePages() ? 'opacity:.4;pointer-events:none;' : '' }}">
        <i class="fas fa-chevron-right" style="font-size:11px;"></i>
      </a>
    </div>
  @endif

@endif

@stop

@section('js')
@include('admission.partials.print_prescription_core')
<script>
var logoUrl = @json(config('app.logo_url'));
var printDataUrl = @json(url('prescriptions/print-data'));
var admissionPrintDataUrl = @json(url('prescriptions/admission-print-data'));

function buildAdmissionPrintHtml(d) {
    var rows = '';
    var meds = d.medicines || [];
    meds.forEach(function(m) {
        rows += admissionPrintMedicineRow(
            m.name, m.morning, m.noon, m.night,
            m.strength, m.timing, m.duration, m.instruction
        );
    });
    rows += admissionPrintBlankMedicineRows(Math.max(0, 8 - meds.length));
    return admissionPrintPrescriptionHtml({
        title: d.title,
        stageTitle: d.stageTitle,
        patientCode: d.patientCode,
        patientName: d.patientName,
        mobile: d.mobile,
        admissionDate: d.admissionDate,
        logoUrl: logoUrl,
        doctorName: d.doctorName,
        doctorSpeciality: d.doctorSpeciality,
        doctorRegNo: d.doctorRegNo,
        doctorContact: d.doctorContact,
        cc: d.cc || '',
        oe: d.oe || '',
        inv: d.inv || '',
        rows: rows
    });
}

function openAdmissionPrintWindow(html, title) {
    var w = window.open('', '_blank', 'width=1000,height=700');
    w.document.open();
    w.document.write(html);
    w.document.close();
    w.document.title = title;
    w.onload = function() {
        w.focus();
        w.print();
        setTimeout(function() { w.close(); }, 500);
    };
}

$(function(){
    $(document).on('click', '.btn-print-rx', function(){
        var id = $(this).data('id');
        $.get(printDataUrl + '/' + id, function(d){
            var html = buildAdmissionPrintHtml(d);
            openAdmissionPrintWindow(html, 'Prescription_' + (d.patientName || ''));
        }).fail(function(xhr){
            alert((xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Print data load failed.');
        });
    });

    $(document).on('click', '.btn-print-adm', function(){
        var btn = $(this);
        $.get(admissionPrintDataUrl, {
            admissionid: btn.data('admissionid'),
            stage: btn.data('stage'),
            table: btn.data('table'),
            patientcode: btn.data('patientcode')
        }, function(d){
            var html = buildAdmissionPrintHtml(d);
            openAdmissionPrintWindow(html, (d.stageTitle || 'Admission') + '_' + (d.patientName || ''));
        }).fail(function(xhr){
            alert((xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Admission print data load failed.');
        });
    });
});
</script>
@stop