@extends('adminlte::page')

@section('title', 'পুরোনো ছাড়পত্র')

@section('content_header')
    <h1>পুরোনো ছাড়পত্র</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <form method="get" class="form-row align-items-end">
            <div class="col-md-4 mb-2"><label>নাম / রোগী কোড / ভর্তি আইডি</label><input name="search" value="{{ request('search') }}" class="form-control"></div>
            <div class="col-md-2 mb-2"><label>শুরুর তারিখ</label><input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control"></div>
            <div class="col-md-2 mb-2"><label>শেষ তারিখ</label><input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control"></div>
            <div class="col-md-4 mb-2"><button class="btn btn-primary"><i class="fas fa-search mr-1"></i> খুঁজুন</button> <a href="{{ route($historyRoute) }}" class="btn btn-secondary">রিসেট</a> <a href="{{ route('admission.discharge') }}" class="btn btn-outline-primary">বর্তমান ছাড়পত্র</a></div>
        </form>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>রোগী</th><th>কোড</th><th>ভর্তি আইডি</th><th>ছাড়পত্রের তারিখ</th><th>ডাক্তার</th><th class="text-right">অ্যাকশন</th></tr></thead>
            <tbody>
            @forelse($history as $row)
                @php
                    $detailUrl = route('web-oldpatient.view', ['admission' => $row->id]);
                    $dischargeDate = $row->discharge_date ?? $row->admission_discharge_date;
                @endphp
                <tr>
                    <td><strong>{{ $row->patientname }}</strong><br><small class="text-muted">{{ $row->mobile_no }}</small></td>
                    <td>{{ $row->patientcode }}</td>
                    <td>{{ $row->admissionid }}</td>
                    <td>{{ $dischargeDate ? date('d-m-Y', strtotime($dischargeDate)) : '-' }}</td>
                    <td>{{ $row->doctor_name ?: '-' }}</td>
                    <td class="text-right"><a href="{{ $detailUrl }}" class="btn btn-sm btn-outline-primary">দেখুন</a> <a href="{{ $detailUrl }}?print=1" target="_blank" class="btn btn-sm btn-info">Print / PDF</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-5">কোনো ছাড়পত্রের রেকর্ড পাওয়া যায়নি।</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($history->hasPages())<div class="card-footer">{{ $history->links() }}</div>@endif
</div>
@stop
