<div class="table-responsive">
    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th>Patient</th>
                <th>Code</th>
                <th>Phone</th>
                <th>Follow-up Date</th>
                <th>Doctor</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $f)
                <tr>
                    <td>{{ $f->patient->patientname ?? '-' }}</td>
                    <td>{{ $f->patient->patientcode ?? '-' }}</td>
                    <td>
                        @if($f->patient->mobile_no)
                            <a href="tel:{{ $f->patient->mobile_no }}">{{ $f->patient->mobile_no }}</a>
                        @else - @endif
                    </td>
                    <td>{{ $f->followup_date->format('d M Y') }}</td>
                    <td>{{ $f->doctor->doctor_name ?? '-' }}</td>
                    <td>
                        <span class="badge badge-{{ $f->status == 'pending' ? 'warning' : ($f->status == 'contacted' ? 'info' : ($f->status == 'completed' ? 'success' : 'secondary')) }}">
                            {{ ucfirst($f->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('usg-reports.show', $f->usg_report_id) }}" class="btn btn-info btn-xs"><i class="fas fa-eye"></i></a>
                        @if($f->status == 'pending')
                            <form action="{{ route('usg-followups.contacted', $f->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-success btn-xs" title="Mark Contacted"><i class="fas fa-phone"></i></button>
                            </form>
                            <form action="{{ route('usg-followups.complete', $f->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-primary btn-xs" title="Complete"><i class="fas fa-check"></i></button>
                            </form>
                            <form action="{{ route('usg-followups.cancel', $f->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel follow-up?');">
                                @csrf
                                <button class="btn btn-secondary btn-xs" title="Cancel"><i class="fas fa-times"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">No records.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
