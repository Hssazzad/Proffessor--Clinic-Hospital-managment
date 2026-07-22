@if($medicines->count() > 0)

<table class="table table-bordered table-striped table-sm">
    <thead class="bg-primary text-white">
        <tr>
            <th>#</th>
            <th>Medicine</th>
            <th>Strength</th>
            <th>Morning</th>
            <th>Noon</th>
            <th>Night</th>
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
            $morning = $_val($doseParts[0] ?? '');
            $noon    = $_val($doseParts[1] ?? '');
            $night   = $_val($doseParts[2] ?? '');
        @endphp
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $m->name }}</td>
            <td>{{ $m->strength }}</td>
            <td>{{ $morning }}</td>
            <td>{{ $noon }}</td>
            <td>{{ $night }}</td>
            <td>
                <button type="button"
                    class="btn btn-warning btn-xs editMedicineBtn"
                    data-id="{{ $m->id }}"
                    data-name="{{ $m->name }}"
                    data-strength="{{ $m->strength }}"
                    data-dose="{{ $m->dose }}"
                    data-morning="{{ $m->morning }}"
                    data-noon="{{ $m->noon }}"
                    data-night="{{ $m->night }}"
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