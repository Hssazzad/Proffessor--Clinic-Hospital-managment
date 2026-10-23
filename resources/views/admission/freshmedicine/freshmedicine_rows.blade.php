@if($medicines->count() > 0)

<div class="mr-table-wrap">
<table class="mr-table">
    <thead>
        <tr>
            <th style="width:40px;">#</th>
            <th>Medication & Dosage</th>
            <th>Morning</th>
            <th>Noon</th>
            <th>Night</th>
            <th>Meal Timing</th>
            <th>Duration</th>
            <th>Instruction</th>
            <th style="width:140px;text-align:right;">Reconciliation</th>
        </tr>
    </thead>

    <tbody>
        @foreach($medicines as $index => $m)
        @php
            $_val = function($v) {
                if ($v === '1' || $v === 1) return '1';
                if ($v === '1/2' || $v === '0.5') return '1/2';
                if ($v === '0' || $v === '' || $v === null) return '0';
                return $v;
            };
            $morningRaw = ($m->morning ?? '');
            $noonRaw    = ($m->noon ?? '');
            $nightRaw   = ($m->night ?? '');
            $morning = $_val($morningRaw);
            $noon    = $_val($noonRaw);
            $night   = $_val($nightRaw);
            $timingValue = !empty($m->timing) ? $m->timing : ($m->meal_timing ?? '');
            $timingLabel = match ($timingValue) {
                'before' => 'Before Meal',
                'after' => 'After Meal',
                'with' => 'With Meal',
                'empty' => 'Empty Stomach',
                default => $timingValue ?: '—',
            };
        @endphp
        <tr
            data-strength="{{ $m->strength ?? '' }}"
            data-timing="{{ $timingValue }}"
            data-duration="{{ $m->duration ?? '' }}"
            data-instruction="{{ $m->instruction ?? '' }}">
            <td style="color:var(--txt-muted);font-weight:700;font-size:12px;">{{ $index + 1 }}</td>
            <td>
                <div class="mr-med-name">{{ $m->name }}</div>
                @if(!empty($m->strength))
                    <div class="mr-med-dosage">{{ $m->strength }}</div>
                @endif
            </td>
            <td>
                <span style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;border-radius:6px;font-size:12px;font-weight:800;{{ $morning && $morning !== '0' ? 'background:var(--primary-lt);color:var(--primary);' : 'background:#f1f5f9;color:var(--txt-muted);' }}">{{ $morning }}</span>
            </td>
            <td>
                <span style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;border-radius:6px;font-size:12px;font-weight:800;{{ $noon && $noon !== '0' ? 'background:var(--primary-lt);color:var(--primary);' : 'background:#f1f5f9;color:var(--txt-muted);' }}">{{ $noon }}</span>
            </td>
            <td>
                <span style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;border-radius:6px;font-size:12px;font-weight:800;{{ $night && $night !== '0' ? 'background:var(--primary-lt);color:var(--primary);' : 'background:#f1f5f9;color:var(--txt-muted);' }}">{{ $night }}</span>
            </td>
            <td style="font-size:12px;font-weight:600;">{{ $timingLabel }}</td>
            <td style="font-size:12px;color:var(--txt-secondary);">{{ $m->duration ?? '—' }}</td>
            <td style="font-size:12px;color:var(--txt-secondary);">{{ $m->instruction ?? '—' }}</td>
            <td style="text-align:right;">
                <div class="mr-recon-actions" style="justify-content:flex-end;">
                    <button type="button"
                        class="mr-action-btn mr-action-edit editMedicineBtn"
                        title="Edit Medicine"
                        data-id="{{ $m->id }}"
                        data-key="{{ $m->key }}"
                        data-name="{{ $m->name }}"
                        data-strength="{{ $m->strength ?? '' }}"
                        data-morning="{{ $morningRaw }}"
                        data-noon="{{ $noonRaw }}"
                        data-night="{{ $nightRaw }}"
                        data-timing="{{ $timingValue }}"
                        data-duration="{{ $m->duration ?? '' }}"
                        data-instruction="{{ $m->instruction ?? '' }}"
                        data-templeteid="{{ $m->templeteid }}"
                        data-order_type="{{ $m->order_type }}">
                        <i class="fas fa-pen"></i>
                    </button>

                    <button type="button"
                        class="mr-action-btn mr-action-delete deleteMedicineBtn"
                        title="Delete Medicine"
                        data-id="{{ $m->id }}"
                        data-key="{{ $m->key }}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>

<div class="mr-recon-footer">
    <span class="mr-recon-count">{{ $medicines->count() }} medication(s) listed</span>
</div>

@else
<div class="mr-empty">
    <i class="fas fa-capsules"></i>
    <p>No medications found for this patient.<br>Use the form above to add medicines.</p>
</div>
@endif