@extends('adminlte::page')

@section('title', 'Operation Consent List')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>অপারেশন সম্মতি ফর্মের তালিকা</h1>
        <a href="{{ route('operation.consent.search') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> নতুন সম্মতি ফর্ম
        </a>
    </div>
@stop

@section('content')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width:50px;">SL</th>
                        <th>রোগীর নাম</th>
                        <th>বয়স/লিঙ্গ</th>
                        <th>Admission ID</th>
                        <th>অপারেশনের নাম</th>
                        <th>অপারেশনের তাং</th>
                        <th>তৈরি হয়েছে</th>
                        <th style="width:100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($consents as $i => $consent)
                        <tr>
                            <td>{{ $consents->firstItem() + $i }}</td>
                            <td>{{ $consent->patientname }}</td>
                            <td>{{ $consent->age }} / {{ $consent->gender }}</td>
                            <td>{{ $consent->admissionid }}</td>
                            <td>{{ $consent->operation_name }}</td>
                            <td>{{ $consent->operation_date ? date('d-m-Y', strtotime($consent->operation_date)) : '' }}</td>
                            <td>{{ $consent->created_at ? date('d-m-Y h:i A', strtotime($consent->created_at)) : '' }}</td>
                            <td>
                                <a href="{{ route('operation.consent.print', $consent->id) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-outline-primary" title="View / Print">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                এখনো কোনো সম্মতি ফর্ম তৈরি হয়নি।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($consents->hasPages())
            <div class="card-footer">
                {{ $consents->links() }}
            </div>
        @endif
    </div>

@stop