@extends('adminlte::page')

@section('title', 'Select Patient - Operation Consent')

@section('content_header')
    <h1>Select Patient — Operation Consent Form</h1>
@stop

@section('content')

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('operation.consent.search') }}" class="form-inline mb-3">
                <input type="text" name="q" value="{{ request('q') }}"
                       class="form-control mr-2" style="width:300px;"
                       placeholder="Patient Name / Admission ID / Patient Code / Mobile">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                @if(request('q'))
                    <a href="{{ route('operation.consent.search') }}" class="btn btn-light ml-2">Clear</a>
                @endif
            </form>

            <table class="table table-striped table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width:50px;">SL</th>
                        <th>Patient Name</th>
                        <th>Age / Gender</th>
                        <th>Admission ID</th>
                        <th>Patient Code</th>
                        <th>Doctor</th>
                        <th>Admission Date</th>
                        <th>Mobile</th>
                        <th style="width:140px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admissions as $i => $a)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $a->patientname }}</td>
                            <td>{{ $a->age }} / {{ $a->gender }}</td>
                            <td>{{ $a->admissionid }}</td>
                            <td>{{ $a->patientcode }}</td>
                            <td>{{ $a->doctor_name }}</td>
                            <td>{{ $a->admission_date ? date('d-m-Y', strtotime($a->admission_date)) : '' }}</td>
                            <td>{{ $a->mobile_no }}</td>
                            <td>
                                <a href="{{ route('operation.consent.create', $a->admission_id) }}"
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-file-medical"></i> Create Form
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                No admission records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@stop