@extends('adminlte::page')

@section('plugins.FontAwesome', false)
@section('title', 'বেড ম্যানেজমেন্ট')

@section('content_header')
    <h1 class="text-primary">বেড ম্যানেজমেন্ট</h1>
@stop

@section('content')
<div class="hospital-page">
<div class="card hospital-box">
    <div class="card-header with-border">
        <div>
            <h3 class="card-title">বেড ম্যানেজমেন্ট</h3>
            <small class="text-muted" style="font-size:13px;">বেড ম্যাট্রিক্স ওভারভিউ</small>
        </div>
        <div class="box-tools pull-right">
            <a href="#addBedModal" class="btn btn-success">
                <i class="fas fa-plus"></i> নতুন বেড
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="bed-management-wrap">

    {{-- ─── Flash Messages ─────────────────────────────────── --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show bmw-alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show bmw-alert">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- ─── Stats Cards ─────────────────────────────────────── --}}
    <div class="bmw-stats-grid">
        <div class="bmw-stat-card">
            <div class="bmw-stat-icon bmw-icon-total"><i class="fas fa-bed"></i></div>
            <div class="bmw-stat-value">{{ $totalBeds }}</div>
            <div class="bmw-stat-label">মোট বেড</div>
        </div>
        <div class="bmw-stat-card">
            <div class="bmw-stat-icon bmw-icon-available"><i class="fas fa-check-circle"></i></div>
            <div class="bmw-stat-value">{{ $availableBeds }}</div>
            <div class="bmw-stat-label">ফাঁকা বেড</div>
        </div>
        <div class="bmw-stat-card">
            <div class="bmw-stat-icon bmw-icon-occupied"><i class="fas fa-user-injured"></i></div>
            <div class="bmw-stat-value">{{ $occupiedBeds }}</div>
            <div class="bmw-stat-label">দখলকৃত বেড</div>
        </div>
        <div class="bmw-stat-card">
            <div class="bmw-stat-icon bmw-icon-maintenance"><i class="fas fa-tools"></i></div>
            <div class="bmw-stat-value">{{ $maintenanceBeds }}</div>
            <div class="bmw-stat-label">রক্ষণাবেক্ষণ</div>
        </div>
    </div>

    {{-- ─── Filter Bar ─────────────────────────────────────── --}}
    @php
        $bedTypes = $beds->pluck('bed_type')->filter()->unique()->values();
    @endphp
    <div class="bmw-filter-bar">
        <button type="button" class="bmw-filter-pill active" data-filter="all">সকল বেড</button>
        @foreach($bedTypes as $type)
            <button type="button" class="bmw-filter-pill" data-filter="{{ \Illuminate\Support\Str::slug($type) }}">{{ $type }}</button>
        @endforeach
    </div>

    {{-- ─── Bed Grid ───────────────────────────────────────── --}}
    <div class="bmw-bed-grid">
        @forelse($beds as $bed)
            <div class="bmw-bed-card" data-type="{{ \Illuminate\Support\Str::slug($bed->bed_type ?? 'general-ward') }}">

                <div class="bmw-bed-card-top">
                    <div>
                        <div class="bmw-bed-id">{{ $bed->bed_number }}</div>
                        @if($bed->bed_name)
                            <div class="bmw-bed-name-text">{{ $bed->bed_name }}</div>
                        @endif
                        <div class="bmw-bed-ward">{{ $bed->bed_type ?? 'General Ward' }}</div>
                    </div>
                    <div class="bmw-badge-row">
                        @if($bed->status === 'vacant')
                            <span class="bmw-badge bmw-badge-available">ফাঁকা</span>
                        @elseif($bed->status === 'occupied')
                            <span class="bmw-badge bmw-badge-occupied">দখলকৃত</span>
                        @else
                            <span class="bmw-badge bmw-badge-maintenance">রক্ষণাবেক্ষণ</span>
                        @endif
                        <a href="#bed-detail-{{ $bed->id }}" class="bmw-detail-icon" title="বিস্তারিত দেখুন">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                </div>

                @if($bed->status === 'occupied')
                    <div class="bmw-bed-card-body">
                        <div class="bmw-patient-name">{{ $bed->currentAdmission?->patient?->patientname ?? 'তথ্য নেই' }}</div>
                        <div class="bmw-patient-id">ID: {{ $bed->currentAdmission?->patient?->patientcode ?? 'N/A' }}</div>
                    </div>
                @elseif($bed->status === 'vacant')
                    <div class="bmw-bed-card-body">
                        <div class="bmw-bed-meta">নতুন রোগী ভর্তির জন্য প্রস্তুত &middot; ৳{{ number_format($bed->price_per_day, 2) }}/দিন</div>
                    </div>
                @else
                    <div class="bmw-bed-card-body">
                        <div class="bmw-bed-meta">রক্ষণাবেক্ষণ চলছে</div>
                    </div>
                @endif

                @if($bed->status === 'vacant')
                    <a href="#bed-detail-{{ $bed->id }}" class="bmw-btn-admit">
                        <i class="fas fa-plus-circle"></i> ভর্তি করুন
                    </a>
                @else
                    <a href="#bed-detail-{{ $bed->id }}" class="bmw-btn-outline">
                        <i class="fas fa-info-circle"></i> বিস্তারিত
                    </a>
                @endif

            </div>
        @empty
            <div class="bmw-empty">কোনো বেড পাওয়া যায়নি।</div>
        @endforelse
    </div>

</div>
</div>
</div>
</div>


{{-- ════════════════════════════════════════════════════════════════
     BED DETAIL MODALS
     ════════════════════════════════════════════════════════════════ --}}
@foreach($beds as $bed)
<div id="bed-detail-{{ $bed->id }}" class="css-modal-overlay">
    <div class="css-modal-box">

        <div class="css-modal-header
            @if($bed->status === 'vacant') bg-success
            @elseif($bed->status === 'occupied') bg-danger
            @else bg-warning
            @endif">
            <span>
                <i class="fas fa-bed mr-2"></i>
                বেড বিস্তারিত — <strong>{{ $bed->bed_number }}</strong> @if($bed->bed_name) ({{ $bed->bed_name }}) @endif
            </span>
            <a href="#" class="css-modal-close">&times;</a>
        </div>

        <div class="css-modal-body">

            <h6 class="section-title"><i class="fas fa-info-circle mr-1"></i> বেডের তথ্য</h6>
            <table class="table table-sm table-bordered mb-3">
                <tbody>
                    <tr>
                        <th class="table-label">বেড নম্বর</th>
                        <td><strong>{{ $bed->bed_number }}</strong></td>
                    </tr>
                    <tr>
                        <th class="table-label">বেডের নাম</th>
                        <td>{{ $bed->bed_name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th class="table-label">বেডের ধরন</th>
                        <td>{{ $bed->bed_type ?? 'General Ward' }}</td>
                    </tr>
                    <tr>
                        <th class="table-label">প্রতিদিনের মূল্য</th>
                        <td>৳ {{ number_format($bed->price_per_day, 2) }}</td>
                    </tr>
                    <tr>
                        <th class="table-label">অবস্থা</th>
                        <td>
                            @if($bed->status === 'vacant')
                                <span class="bmw-badge bmw-badge-available">ফাঁকা</span>
                            @elseif($bed->status === 'occupied')
                                <span class="bmw-badge bmw-badge-occupied">দখলকৃত</span>
                            @else
                                <span class="bmw-badge bmw-badge-maintenance">রক্ষণাবেক্ষণ</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>

            @if($bed->status === 'occupied')
                @php
                    $admission = $bed->currentAdmission;
                    $patient   = $admission?->patient;
                @endphp

                @if($patient)
                    <h6 class="section-title"><i class="fas fa-user mr-1"></i> রোগীর তথ্য</h6>
                    <table class="table table-sm table-bordered mb-3">
                        <tbody>
                            <tr>
                                <th class="table-label">রোগীর নাম</th>
                                <td><strong>{{ $patient->patientname }}</strong></td>
                            </tr>
                            <tr>
                                <th class="table-label">পেশেন্ট কোড</th>
                                <td>{{ $patient->patientcode }}</td>
                            </tr>
                            <tr>
                                <th class="table-label">ভর্তি আইডি</th>
                                <td>{{ $admission->admissionid }}</td>
                            </tr>
                            <tr>
                                <th class="table-label">বয়স</th>
                                <td>{{ $patient->age }}</td>
                            </tr>
                            <tr>
                                <th class="table-label">লিঙ্গ</th>
                                <td>{{ $patient->gender ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="table-label">মোবাইল</th>
                                <td>{{ $patient->mobile_no ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="table-label">রক্তের গ্রুপ</th>
                                <td>{{ $patient->blood_group ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="table-label">ঠিকানা</th>
                                <td>{{ $patient->address ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="table-label">ডাক্তার</th>
                                <td>{{ $admission->doctor_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="table-label">বিভাগ</th>
                                <td>{{ $admission->department ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>

                    @php $vacantBeds = $beds->where('status', 'vacant'); @endphp

                    <h6 class="section-title" style="color:#c0392b;"><i class="fas fa-exchange-alt mr-1"></i> বেড পরিবর্তন করুন</h6>

                    @if($vacantBeds->count() > 0)
                        <form action="{{ route('admission.patient_accomodation.changeBed') }}" method="POST">
                            @csrf
                            <input type="hidden" name="admission_id" value="{{ $admission->id }}">
                            <div class="form-group">
                                <label class="font-weight-bold">নতুন বেড নির্বাচন করুন <span class="text-danger">*</span></label>
                                <select name="new_bed_id" class="form-control form-control-sm bmw-input" required>
                                    <option value="">-- ফাঁকা বেড নির্বাচন করুন --</option>
                                    @foreach($vacantBeds as $vb)
                                        <option value="{{ $vb->id }}">
                                            {{ $vb->bed_number }} {{ $vb->bed_name ? '('.$vb->bed_name.')' : '' }}
                                            ({{ $vb->bed_type ?? 'General' }})
                                            — ৳{{ number_format($vb->price_per_day, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="bmw-btn-danger-full">
                                <i class="fas fa-exchange-alt mr-1"></i> বেড পরিবর্তন নিশ্চিত করুন
                            </button>
                        </form>
                    @else
                        <div class="alert alert-warning text-center mb-0 bmw-alert">
                            <i class="fas fa-exclamation-triangle mr-1"></i> এখন কোনো ফাঁকা বেড নেই।
                        </div>
                    @endif

                @else
                    <div class="alert alert-danger text-center bmw-alert">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        রোগীর তথ্য পাওয়া যায়নি। Relationship সমস্যা থাকতে পারে।
                    </div>
                @endif

            @elseif($bed->status === 'vacant')
                <div class="alert alert-success text-center bmw-alert">
                    <i class="fas fa-check-circle mr-1"></i> এই বেডটি বর্তমানে ফাঁকা আছে।
                </div>

            @else
                <div class="alert alert-warning text-center bmw-alert">
                    <i class="fas fa-tools mr-1"></i> এই বেডটি রক্ষণাবেক্ষণে আছে।
                </div>
            @endif

        </div>
    </div>
</div>
@endforeach


{{-- ─── Add New Bed Modal ──────── --}}
<div id="addBedModal" class="css-modal-overlay">
    <div class="css-modal-box" style="max-width:480px;">
        <div class="css-modal-header" style="background:#075F3F;">
            <span><i class="fas fa-bed mr-2"></i> নতুন বেড যোগ করুন</span>
            <a href="#" class="css-modal-close">&times;</a>
        </div>
        <div class="css-modal-body">
            <form action="{{ route('admission.patient_accomodation.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>বেড নম্বর <span class="text-danger">*</span></label>
                    <input type="text" name="bed_number" class="form-control form-control-sm bmw-input
                        @error('bed_number') is-invalid @enderror"
                        value="{{ old('bed_number') }}"
                        placeholder="যেমন: W-105" required>
                    @error('bed_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Added bed_name field --}}
                <div class="form-group">
                    <label>বেডের নাম</label>
                    <input type="text" name="bed_name" class="form-control form-control-sm bmw-input
                        @error('bed_name') is-invalid @enderror"
                        value="{{ old('bed_name') }}"
                        placeholder="যেমন: VIP Deluxe Bed-A1">
                    @error('bed_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>বেডের ধরন <span class="text-danger">*</span></label>
                    <select name="bed_type" class="form-control form-control-sm bmw-input
                        @error('bed_type') is-invalid @enderror" required>
                        <option value="">-- নির্বাচন করুন --</option>
                        <option value="General Ward" {{ old('bed_type') == 'General Ward' ? 'selected' : '' }}>General Ward</option>
                        <option value="ICU"          {{ old('bed_type') == 'ICU'          ? 'selected' : '' }}>ICU</option>
                        <option value="CCU"          {{ old('bed_type') == 'CCU'          ? 'selected' : '' }}>CCU</option>
                        <option value="HDU"          {{ old('bed_type') == 'HDU'          ? 'selected' : '' }}>HDU</option>
                        <option value="Private Cabin"{{ old('bed_type') == 'Private Cabin'? 'selected' : '' }}>Private Cabin</option>
                        <option value="Emergency"    {{ old('bed_type') == 'Emergency'    ? 'selected' : '' }}>Emergency</option>
                    </select>
                    @error('bed_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>প্রতিদিনের মূল্য (৳)</label>
                    <input type="number" name="price_per_day" class="form-control form-control-sm bmw-input"
                        value="{{ old('price_per_day', 0) }}" step="0.01" min="0">
                </div>
                <div class="d-flex justify-content-between">
                    <a href="#" class="bmw-btn-cancel">বাতিল করুন</a>
                    <button type="submit" class="bmw-btn-primary" style="background:#075F3F;border:1px solid #075F3F;color:#fff;border-radius:3px;padding:8px 16px;font-size:14px;font-weight:600;">
                        <i class="fas fa-save mr-1"></i> বেড সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/hospital-erp.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
.bed-management-wrap, .bed-management-wrap * { font-family: inherit; }

.bmw-stats-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:18px;}
@media(min-width:768px){.bmw-stats-grid{grid-template-columns:repeat(4,1fr);}}
.bmw-stat-card{background:#ffffff;border:1px solid #dddddd;border-radius:4px;padding:14px 16px;box-shadow:0 1px 2px rgba(0,0,0,0.05);}
.bmw-stat-icon{width:32px;height:32px;border-radius:4px;display:flex;align-items:center;justify-content:center;margin-bottom:8px;font-size:15px;}
.bmw-icon-total{background:#e8f5ef;color:#075F3F;}
.bmw-icon-available{background:#e8f5ef;color:#198754;}
.bmw-icon-occupied{background:#fdecea;color:#c0392b;}
.bmw-icon-maintenance{background:#fdf3e0;color:#c9890a;}
.bmw-stat-value{font-size:18px;font-weight:600;color:#222222;line-height:1.2;}
.bmw-stat-label{font-size:13px;color:#666666;font-weight:500;margin-top:2px;}

.bmw-filter-bar{display:flex;gap:8px;overflow-x:auto;padding-bottom:6px;margin-bottom:16px;}
.bmw-filter-pill{flex:0 0 auto;background:#ffffff;border:1px solid #dddddd;border-radius:3px;padding:7px 16px;font-size:14px;font-weight:500;color:#444444;cursor:pointer;white-space:nowrap;}
.bmw-filter-pill:hover{border-color:#075F3F;color:#075F3F;}
.bmw-filter-pill.active{background:#075F3F;border-color:#075F3F;color:#ffffff;font-weight:600;}

.bmw-bed-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:14px;}
.bmw-bed-card{background:#ffffff;border:1px solid #dddddd;border-radius:4px;padding:14px;display:flex;flex-direction:column;box-shadow:0 1px 2px rgba(0,0,0,0.05);}
.bmw-bed-card:hover{border-color:#9fc9b4;}
.bmw-bed-card-top{display:flex;justify-content:space-between;align-items:flex-start;}
.bmw-bed-id{font-size:15px;font-weight:600;color:#222222;}
.bmw-bed-name-text{font-size:13px;font-weight:500;color:#075F3F;}
.bmw-bed-ward{font-size:13px;color:#666666;font-weight:400;margin-top:2px;}
.bmw-badge{font-size:13px;font-weight:600;padding:4px 10px;border-radius:3px;white-space:nowrap;}
.bmw-badge-available{background:#e8f5ef;color:#075F3F;}
.bmw-badge-occupied{background:#fdecea;color:#c0392b;}
.bmw-badge-maintenance{background:#fdf3e0;color:#8a5a00;}
.bmw-bed-card-body{border-top:1px solid #eaeaea;margin-top:12px;padding-top:10px;}
.bmw-patient-name{font-size:14px;font-weight:600;color:#222222;}
.bmw-patient-id{font-size:13px;color:#666666;margin-top:2px;}
.bmw-bed-meta{font-size:13px;color:#666666;}
.bmw-btn-admit{margin-top:12px;background:#075F3F;border:1px solid #075F3F;color:#fff;border-radius:3px;padding:8px;font-size:13px;font-weight:600;text-align:center;text-decoration:none;}
.bmw-btn-admit:hover{background:#064A32;color:#fff;text-decoration:none;}
.bmw-btn-outline{margin-top:12px;background:#ffffff;border:1px solid #075F3F;color:#075F3F;border-radius:3px;padding:8px;font-size:13px;font-weight:600;text-align:center;text-decoration:none;}
.bmw-btn-outline:hover{background:#e8f5ef;color:#075F3F;text-decoration:none;}
.bmw-btn-danger-full{width:100%;background:#c0392b;border:1px solid #c0392b;color:#fff;border-radius:3px;padding:9px;font-size:14px;font-weight:600;}
.bmw-btn-danger-full:hover{background:#a93226;color:#fff;}
.bmw-btn-cancel{background:#ffffff;color:#555555;border:1px solid #dddddd;border-radius:3px;padding:8px 16px;font-size:14px;font-weight:500;text-decoration:none;}
.bmw-btn-cancel:hover{background:#f7f7f7;color:#444444;text-decoration:none;}
.bmw-empty{grid-column:1/-1;background:#ffffff;border:1px solid #dddddd;border-radius:4px;padding:30px;text-align:center;color:#666666;font-size:14px;}

.bmw-alert{border-radius:3px;}
.bmw-input{border-radius:3px;border:1px solid #dddddd;font-size:14px;height:34px;}

.css-modal-overlay{display:none;position:fixed;inset:0;background:rgba(7,95,63,0.45);z-index:9999;overflow-y:auto;padding:40px 16px;}
.css-modal-overlay:target{display:block;}
.css-modal-box{background:#ffffff;max-width:620px;margin:0 auto;border-radius:4px;border:1px solid #dddddd;overflow:hidden;font-family:inherit;box-shadow:0 4px 12px rgba(0,0,0,0.18);}
.css-modal-header{color:#fff;padding:14px 20px;display:flex;justify-content:space-between;align-items:center;font-size:15px;font-weight:600;}
.css-modal-close{color:#fff;font-size:22px;line-height:1;text-decoration:none;font-weight:300;opacity:.85;}
.css-modal-close:hover{opacity:1;color:#fff;text-decoration:none;}
.css-modal-body{padding:18px 22px;}
.section-title{font-weight:600;font-size:14px;border-bottom:1px solid #dddddd;padding-bottom:6px;margin-bottom:12px;color:#333333;}
.table-label{width:38%;background:#f2faf6;font-weight:500;color:#444444;font-size:13px;}

.bmw-badge-row{display:flex;align-items:center;gap:6px;}
.bmw-detail-icon{width:26px;height:26px;border-radius:3px;background:#ffffff;border:1px solid #dddddd;color:#075F3F;display:flex;align-items:center;justify-content:center;font-size:12px;text-decoration:none;}
.bmw-detail-icon:hover{background:#e8f5ef;color:#075F3F;text-decoration:none;}
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var pills = document.querySelectorAll('.bmw-filter-pill');
    var cards = document.querySelectorAll('.bmw-bed-card');

    pills.forEach(function (pill) {
        pill.addEventListener('click', function () {
            pills.forEach(function (p) { p.classList.remove('active'); });
            pill.classList.add('active');

            var filter = pill.getAttribute('data-filter');

            cards.forEach(function (card) {
                if (filter === 'all' || card.getAttribute('data-type') === filter) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
});
</script>
@stop