@extends('adminlte::page')

@section('plugins.Select2', true)
@section('title', 'Fresh Prescription')

@push('css')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
    :root{
        --bg:#f0f4f8;
        --surf:#ffffff;
        --bdr:#e2e8f0;
        --bdr-strong:#cbd5e1;
        --txt:#1e293b;
        --txt-secondary:#64748b;
        --primary:#0f52ba;
        --primary-dk:#0a3d8f;
        --primary-lt:#e0ecfb;
        --r:10px;
        --r-sm:6px;
        --shadow-sm:0 1px 2px rgba(0,0,0,.05);
        --shadow:0 1px 3px rgba(0,0,0,.1),0 1px 2px rgba(0,0,0,.06);
        --font:'Inter',system-ui,-apple-system,sans-serif;
    }
    body,input,select,textarea,button{font-family:var(--font)!important;}

    .select2-container--bootstrap4 .select2-selection {
        border: 1px solid var(--bdr-strong) !important;
        border-radius: var(--r-sm) !important;
        min-height: 42px !important;
        padding: 5px !important;
        font-family: var(--font) !important;
    }
    .select2-container--bootstrap4 .select2-selection:focus,
    .select2-container--bootstrap4.select2-container--focus .select2-selection{
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px rgba(15,82,186,.1) !important;
    }
    .mr-select-page{
        min-height:80vh;
        display:flex;align-items:center;justify-content:center;
        padding:40px 20px;
    }
    .mr-select-card{
        background:var(--surf);
        border:1px solid var(--bdr);
        border-radius:var(--r);
        box-shadow:var(--shadow);
        width:100%;max-width:560px;
        overflow:hidden;
    }
    .mr-select-header{
        background:var(--primary);
        padding:24px 28px;
        text-align:center;
    }
    .mr-select-header i{
        font-size:32px;color:rgba(255,255,255,.7);
        margin-bottom:10px;display:block;
    }
    .mr-select-header h2{
        color:#fff;font-size:20px;font-weight:800;
        letter-spacing:-.3px;margin:0 0 4px;
    }
    .mr-select-header p{
        color:rgba(255,255,255,.7);font-size:13px;
        font-weight:500;margin:0;
    }
    .mr-select-body{padding:28px;}
    .mr-select-label{
        display:block;font-size:11px;font-weight:700;
        letter-spacing:.5px;text-transform:uppercase;
        color:var(--txt-secondary);margin-bottom:8px;
    }
    .mr-select-label .req{color:#dc2626;}
    .mr-select-btn{
        display:flex;align-items:center;justify-content:center;gap:8px;
        width:100%;margin-top:20px;
        background:var(--primary);color:#fff;
        border:none;border-radius:var(--r-sm);
        padding:12px 24px;font-size:14px;font-weight:700;
        cursor:pointer;transition:all .15s;
    }
    .mr-select-btn:hover{background:var(--primary-dk);}
</style>
@endpush

@section('content_header')
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success" style="border-radius:6px;font-weight:600;">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger" style="border-radius:6px;font-weight:600;">{{ session('error') }}</div>
@endif

<div class="mr-select-page">
    <div class="mr-select-card">
        <div class="mr-select-header">
            <i class="fas fa-pills"></i>
            <h2>Fresh Prescription</h2>
            <p>Select a patient to manage fresh prescriptions</p>
        </div>
        <div class="mr-select-body">
            <form method="POST" action="{{ route('admission.showfreshMedicine') }}">
                @csrf

                <label class="mr-select-label">Select Patient <span class="req">*</span></label>
                <select name="patientcode" id="patientcode" class="form-control select2" required>
                    <option value="">-- Search by Name or Mobile --</option>
                    @foreach($patients as $p)
                        <option value="{{ $p->patientcode }}">
                            {{ $p->patientname }} - {{ $p->patientcode ?? '' }} ({{ $p->mobile_no ?? 'No Mobile' }})
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="mr-select-btn">
                    <i class="fas fa-arrow-right"></i> Load Medicine
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
$(document).ready(function () {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
});
</script>
@endpush