{{-- resources/views/Cesprescriptions/tabs/preview.blade.php --}}
{{--
    Preview tab: embeds the canonical A4 prescription sheet (prescriptions.print)
    in an iframe. The sheet is the single source of truth for the layout, so what
    you see here is byte-for-byte what gets printed. Toolbar offers only Back and
    Print — no extra tabs, no windows, no PDF.
--}}
@php
  $pid       = $pid ?? request('id');
  $patientId = $patientId ?? request('patient');
  $rx = $rx ?? null;
  $patient = $patient ?? null;
  $complaints = $complaints ?? collect();
  $diagnoses = $diagnoses ?? collect();
  $investigations = $investigations ?? collect();
  $medicines = $medicines ?? collect();
  $validTabs = ['patients','complain','oe','investigations','diagnosis','medicine','preview','finish'];
  $backTab = request('back_tab', 'medicine');
  $backTab = in_array($backTab, $validTabs, true) ? $backTab : 'medicine';
@endphp

<div class="rx-preview">
  @if(!$pid)
    <div class="alert alert-warning">Prescription ID not found.</div>
  @else
    <div class="rx-preview-toolbar rx-no-print">
      <span class="rx-preview-title">
        <i class="fas fa-file-prescription"></i> Prescription Preview
      </span>
      <div class="rx-preview-actions">
        <a class="rx-btn secondary" href="{{ route('rx.wizard', ['id' => $pid, 'patient' => $patientId, 'tab' => $backTab, 'back_tab' => $backTab]) }}">
          <i class="fas fa-arrow-left"></i> Back
        </a>
        <button type="button" class="rx-btn" id="rxPrintBtn">
          <i class="fas fa-print"></i> Print
        </button>
      </div>
    </div>

    <div class="rx-preview-sheet">
      @php
          // Cesarean wizard mapping — same partials/rx_page pattern as the normal
          // wizard preview. No $oeText / $rxDoctors are passed by the ces show():
          // rx_page shows its default O/E (Pulse:/BP:) and default signature label.
          $rxPatientCode   = trim((string) ($patient->patientcode ?? ''));
          $rxPatientName   = trim((string) ($patient->patientname ?? ''));
          $rxPatientMobile = trim((string) ($patient->mobile_no ?? ''));
          $rxPatientAge    = trim((string) ($patient->age ?? ''));

          $rxDate = \Carbon\Carbon::parse($rx->prescribed_on ?? $rx->created_at ?? now())->format('d-m-Y');

          $rxCC = trim(preg_replace('/[\x{E000}-\x{F8FF}]/u', '', (string) ($rx->chief_complaint ?? '')));
          if ($rxCC === '') {
              $rxCC = collect($complaints ?? [])
                  ->map(fn($c) => trim((string) ($c->note ?? '')) ?: trim((string) ($c->name ?? $c->complaint ?? '')))
                  ->filter()
                  ->map(fn($v) => '• ' . $v)
                  ->implode("\n");
          }

          $rxDx = trim(preg_replace('/[\x{E000}-\x{F8FF}]/u', '', (string) ($rx->diagnosis ?? '')));
          if ($rxDx === '') {
              $rxDx = collect($diagnoses ?? [])
                  ->map(fn($d) => trim((string) ($d->note ?? '')) ?: trim((string) ($d->name ?? '')))
                  ->filter()
                  ->map(fn($v) => '• ' . $v)
                  ->implode("\n");
          }
          if (trim((string) ($rx->differential_diagnosis ?? '')) !== '') {
              $rxDx = trim($rxDx . "\n" . 'D/D: ' . trim((string) $rx->differential_diagnosis));
          }

          $rxInv = collect($investigations ?? [])
              ->map(fn($i) => trim((string) ($i->note ?? '')) ?: trim((string) ($i->name ?? '')))
              ->filter()
              ->map(fn($v) => '• ' . $v)
              ->implode("\n");

          $rxNotes = collect()
              ->push(['title' => 'Advice', 'body' => trim((string) ($rx->advice ?? ''))])
              ->push(['title' => 'Follow Up', 'body' => trim((string) ($rx->follow_up ?? ''))])
              ->filter(fn($n) => trim($n['body'] ?? '') !== '')
              ->values();
      @endphp

      @include('partials.rx_styles')
      @include('partials.rx_page', [
          'rxPatientCode'    => $rxPatientCode,
          'rxPatientName'    => $rxPatientName,
          'rxPatientMobile'  => $rxPatientMobile,
          'rxPatientAge'     => $rxPatientAge,
          'rxDate'           => $rxDate,
          'rxCC'             => $rxCC,
          'rxOE'             => '',
          'rxDx'             => $rxDx,
          'rxInv'            => $rxInv,
          'rxMedicines'      => $medicines ?? collect(),
          'rxMinRows'        => 0,
          'rxNotes'          => $rxNotes,
          'rxSignatureLabel' => '',
      ])
    </div>
  @endif
</div>

<style>
  .rx-preview {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
  }

  .rx-preview-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: .75rem;
    padding: .65rem .85rem;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: .6rem;
    box-shadow: 0 1px 3px rgba(15, 23, 42, .06);
  }

  .rx-preview-title {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    font-weight: 700;
    font-size: 14px;
    color: #0f172a;
  }

  .rx-preview-title i { color: #0ea5e9; }

  .rx-preview-actions { display: flex; gap: .5rem; }

  .rx-btn {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .5rem 1rem;
    border-radius: .5rem;
    border: 1px solid #cbd5e1;
    background: #0ea5e9;
    color: #fff;
    cursor: pointer;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    line-height: 1;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .08);
    transition: filter .15s, background .15s;
  }

  .rx-btn:hover { filter: brightness(1.06); color: #fff; }
  .rx-btn:active { transform: translateY(1px); }

  .rx-btn.secondary {
    background: #fff;
    color: #334155;
  }

  .rx-btn.secondary:hover { background: #f1f5f9; color: #0f172a; }

  .rx-preview-sheet {
    border: 1px solid #e2e8f0;
    border-radius: .6rem;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 6px 20px rgba(15, 23, 42, .10);
    padding: 12px;
  }

  @media print {
    .rx-no-print { display: none !important; }
  }
</style>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const btn = document.getElementById('rxPrintBtn');
  if (btn) {
    btn.addEventListener('click', function () {
      window.print();
    });
  }
});
</script>
@endpush
