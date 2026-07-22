Admit Medicine
@if($medicines->count() > 0)

<table class="table table-bordered table-striped table-sm">
    <thead class="bg-primary text-white">
        <tr>
            <th>#</th>
            <th>Medicine</th>
            <th>Morning</th>
            <th>Noon</th>
            <th>Night</th>
            <th>আগে/পরে</th>
            <th>কতদিন</th>
            <th width="120">Action</th>
        </tr>
    </thead>

    <tbody>
        @foreach($medicines as $index => $m)
        @php
            $_val = function($v) {
                if ($v === '1' || $v === 1) return '১ টি (1)';
                if ($v === '1/2' || $v === '0.5') return 'অর্ধেক (1/2)';
                if ($v === '0' || $v === '' || $v === null) return '০ (চলবে না)';
                return $v;
            };
            $doseParts = explode('+', str_replace('-', '+', $m->dose ?? ''));
            $morningRaw = ($m->morning ?? '') !== '' ? $m->morning : ($doseParts[0] ?? '');
            $noonRaw    = ($m->noon ?? '') !== '' ? $m->noon : ($doseParts[1] ?? '');
            $nightRaw   = ($m->night ?? '') !== '' ? $m->night : ($doseParts[2] ?? '');
            $morning = $_val($morningRaw);
            $noon    = $_val($noonRaw);
            $night   = $_val($nightRaw);
            $timingValue = !empty($m->timing) ? $m->timing : ($m->meal_timing ?? '');
            $timingLabel = match ($timingValue) {
                'before' => 'আগে',
                'after' => 'পরে',
                'with' => 'সাথে',
                'empty' => 'খালি পেটে',
                default => $timingValue,
            };
        @endphp
        <tr
            data-strength="{{ $m->strength ?? '' }}"
            data-timing="{{ $timingValue }}"
            data-duration="{{ $m->duration ?? '' }}"
            data-instruction="{{ $m->instruction ?? '' }}">
            <td>{{ $index + 1 }}</td>
            <td>{{ $m->name }}</td>
            <td>{{ $morning }}</td>
            <td>{{ $noon }}</td>
            <td>{{ $night }}</td>
            <td>{{ $timingLabel }}</td>
            <td>{{ $m->duration }}</td>
            <td>
                <button type="button"
                    class="btn btn-warning btn-xs editMedicineBtn"
                    data-id="{{ $m->id }}"
                    data-name="{{ $m->name }}"
                    data-strength="{{ $m->strength ?? '' }}"
                    data-dose="{{ $m->dose }}"
                    data-morning="{{ $morningRaw }}"
                    data-noon="{{ $noonRaw }}"
                    data-night="{{ $nightRaw }}"
                    data-timing="{{ $timingValue }}"
                    data-duration="{{ $m->duration ?? '' }}"
                    data-instruction="{{ $m->instruction ?? '' }}"
                    data-templeteid="{{ $m->templeteid }}"
                    data-order_type="{{ $m->order_type }}">
                    Edit
                </button>

                <button type="button"
                    class="btn btn-danger btn-xs deleteMedicineBtn"
                    data-id="{{ $m->id }}">
                    Delete
                </button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@else
<div class="alert alert-warning">
    No medicine found for this patient.
</div>
@endif
