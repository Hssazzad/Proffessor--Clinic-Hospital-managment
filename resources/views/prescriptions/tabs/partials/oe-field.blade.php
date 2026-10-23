<div class="col-md-4 mb-3" id="oeFieldWrap_{{ $master['id'] }}">
    <div class="d-flex justify-content-between align-items-start">
        <label class="form-label fw-semibold">{{ $master['name'] }}@if(($master['unit'] ?? '') !== '') <small class="text-muted">({{ $master['unit'] }})</small>@endif</label>
        <button type="button" class="btn btn-sm btn-outline-danger ms-2" title="Remove" onclick="removeOeField({{ $master['id'] }})">&times;</button>
    </div>
    @php($key = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim((string) ($master['short_name'] !== '' ? $master['short_name'] : $master['name'])))))
    @if($master['input_type'] === 'textarea')
        <textarea name="oe[{{ $master['id'] }}]" rows="2" class="form-control" data-oe-key="{{ trim($key, '_') }}">{{ old('oe.' . $master['id'], $value ?? '') }}</textarea>
    @elseif($master['input_type'] === 'number')
        <input type="number" name="oe[{{ $master['id'] }}]" class="form-control" data-oe-key="{{ trim($key, '_') }}" value="{{ old('oe.' . $master['id'], $value ?? '') }}">
    @elseif($master['input_type'] === 'decimal')
        <input type="number" step="any" name="oe[{{ $master['id'] }}]" class="form-control" data-oe-key="{{ trim($key, '_') }}" value="{{ old('oe.' . $master['id'], $value ?? '') }}">
    @else
        <input type="text" name="oe[{{ $master['id'] }}]" class="form-control" data-oe-key="{{ trim($key, '_') }}" value="{{ old('oe.' . $master['id'], $value ?? '') }}">
    @endif
</div>
