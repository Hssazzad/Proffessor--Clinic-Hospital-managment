@php
    $editing = isset($oe) && $oe->exists;
    $action  = $editing ? route('settings.oe.update', $oe) : route('settings.oe.store');
@endphp

<div class="card card-primary card-outline">
    <div class="card-header">
        <h5 class="card-title mb-0 font-weight-bold">
            <i class="fas {{ $editing ? 'fa-edit' : 'fa-plus-circle' }} mr-1"></i>
            {{ $editing ? 'Edit O/E Master' : 'Add New O/E Master' }}
        </h5>
    </div>

    <form method="POST" action="{{ $action }}" class="needs-validation" novalidate>
        @csrf
        @if($editing)
            @method('PUT')
        @endif

        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-tag mr-1 text-muted"></i> Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $oe->name ?? '') }}"
                               placeholder="e.g. Pulse" required maxlength="100">
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-hashtag mr-1 text-muted"></i> Field Code <span class="text-danger">*</span>
                        </label>
                        <select name="field_code"
                                class="form-control @error('field_code') is-invalid @enderror" required>
                            <option value="">-- Select Field Code --</option>
                            @foreach($fieldCodes as $code => $label)
                                <option value="{{ $code }}"
                                    {{ (string) old('field_code', $oe->field_code ?? '') === (string) $code ? 'selected' : '' }}>
                                    {{ $label }} ({{ $code }})
                                </option>
                            @endforeach
                        </select>
                        @error('field_code')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-font mr-1 text-muted"></i> Short Name
                        </label>
                        <input type="text" name="short_name"
                               class="form-control @error('short_name') is-invalid @enderror"
                               value="{{ old('short_name', $oe->short_name ?? '') }}"
                               placeholder="e.g. P" maxlength="30">
                        @error('short_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-ruler-combined mr-1 text-muted"></i> Unit
                        </label>
                        <input type="text" name="unit"
                               class="form-control @error('unit') is-invalid @enderror"
                               value="{{ old('unit', $oe->unit ?? '') }}"
                               placeholder="e.g. bpm" maxlength="20">
                        @error('unit')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-keyboard mr-1 text-muted"></i> Input Type <span class="text-danger">*</span>
                        </label>
                        <select name="input_type"
                                class="form-control @error('input_type') is-invalid @enderror" required>
                            <option value="">-- Select Input Type --</option>
                            @foreach(['text', 'number', 'decimal', 'textarea'] as $type)
                                <option value="{{ $type }}"
                                    {{ old('input_type', $oe->input_type ?? '') === $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                        @error('input_type')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-sort-numeric-up mr-1 text-muted"></i> Sort Order <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="sort_order"
                               class="form-control @error('sort_order') is-invalid @enderror"
                               value="{{ old('sort_order', $oe->sort_order ?? '') }}"
                               placeholder="e.g. 1" required>
                        @error('sort_order')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group mb-0">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="active" name="active" value="1"
                        {{ old('active', $oe->active ?? 1) ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold" for="active">
                        Active
                    </label>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-primary font-weight-bold">
                <i class="fas fa-save mr-1"></i> Save
            </button>
            <a href="{{ route('settings.oe.index') }}" class="btn btn-secondary font-weight-bold">
                <i class="fas fa-times mr-1"></i> Cancel
            </a>
        </div>
    </form>
</div>
