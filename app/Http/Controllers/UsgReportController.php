<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\DoctorNotification;
use App\Models\Patient;
use App\Models\UsgFollowup;
use App\Models\UsgReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsgReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = UsgReport::query()
            ->with(['patient', 'doctor', 'followup']);

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('report_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('report_date', '<=', $request->date_to);
        }

        if ($request->filled('followup_status')) {
            if ($request->followup_status === 'with') {
                $query->whereNotNull('followup_date');
            } elseif ($request->followup_status === 'without') {
                $query->whereNull('followup_date');
            } elseif ($request->followup_status === 'due') {
                $query->whereNotNull('followup_date')
                    ->whereDate('followup_date', '<=', now()->toDateString());
            } elseif ($request->followup_status === 'upcoming') {
                $query->whereNotNull('followup_date')
                    ->whereDate('followup_date', '>', now()->toDateString());
            }
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->whereHas('patient', function ($p) use ($q) {
                $p->where('patientname', 'like', "%{$q}%")
                    ->orWhere('patientcode', 'like', "%{$q}%")
                    ->orWhere('mobile_no', 'like', "%{$q}%");
            });
        }

        $reports = $query->orderByDesc('report_date')->paginate(20)->withQueryString();

        $doctors = Doctor::where('active', 1)->orderBy('doctor_name')->get();

        return view('usg.index', compact('reports', 'doctors'));
    }

    public function create(): View
    {
        $doctors = Doctor::where('active', 1)->orderBy('doctor_name')->get();
        $patients = Patient::orderBy('patientname')->limit(500)->get();

        return view('usg.form', [
            'report' => null,
            'doctors' => $doctors,
            'patients' => $patients,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateReport($request);

        $patient = Patient::findOrFail($data['patient_id']);

        DB::transaction(function () use ($data, $patient) {
            $report = UsgReport::create([
                'patient_id' => $patient->id,
                'patientcode' => $patient->patientcode,
                'doctor_id' => $data['doctor_id'],
                'report_date' => $data['report_date'],
                'findings' => $data['findings'],
                'impression' => $data['impression'] ?? null,
                'followup_date' => $data['followup_date'] ?? null,
                'status' => 'pending',
                'created_by' => Auth::id(),
            ]);

            if (!empty($data['followup_date'])) {
                $this->createFollowup($report, $data['followup_date']);
            }
        });

        return redirect()->route('usg-reports.index')
            ->with('success', 'USG report saved successfully.');
    }

    public function show(UsgReport $usgReport): View
    {
        $usgReport->load(['patient', 'doctor', 'creator', 'followup.contactedBy']);

        return view('usg.show', compact('usgReport'));
    }

    public function edit(UsgReport $usgReport): View
    {
        $doctors = Doctor::where('active', 1)->orderBy('doctor_name')->get();
        $patients = Patient::orderBy('patientname')->limit(500)->get();

        return view('usg.form', [
            'report' => $usgReport,
            'doctors' => $doctors,
            'patients' => $patients,
        ]);
    }

    public function update(Request $request, UsgReport $usgReport): RedirectResponse
    {
        $data = $this->validateReport($request);

        $patient = Patient::findOrFail($data['patient_id']);

        DB::transaction(function () use ($data, $patient, $usgReport) {
            $usgReport->update([
                'patient_id' => $patient->id,
                'patientcode' => $patient->patientcode,
                'doctor_id' => $data['doctor_id'],
                'report_date' => $data['report_date'],
                'findings' => $data['findings'],
                'impression' => $data['impression'] ?? null,
                'followup_date' => $data['followup_date'] ?? null,
            ]);

            $followup = $usgReport->followup;

            if (empty($data['followup_date'])) {
                if ($followup && $followup->status === 'pending') {
                    $followup->notifications()->delete();
                    $followup->update(['status' => 'cancelled']);
                }
                return;
            }

            if ($followup) {
                $followup->update([
                    'followup_date' => $data['followup_date'],
                    'doctor_id' => $data['doctor_id'],
                    'patient_id' => $patient->id,
                    'status' => 'pending',
                ]);
                $followup->notifications()->delete();
                $this->createFollowupNotifications($followup);
            } else {
                $this->createFollowup($usgReport, $data['followup_date']);
            }
        });

        return redirect()->route('usg-reports.index')
            ->with('success', 'USG report updated successfully.');
    }

    public function destroy(UsgReport $usgReport): RedirectResponse
    {
        DB::transaction(function () use ($usgReport) {
            if ($usgReport->followup) {
                $usgReport->followup->notifications()->delete();
            }
            $usgReport->delete();
        });

        return redirect()->route('usg-reports.index')
            ->with('success', 'USG report deleted.');
    }

    public function patientSearch(Request $request)
    {
        $q = trim((string) $request->get('q'));

        $patients = Patient::query()
            ->when($q, function ($query) use ($q) {
                $query->where('patientname', 'like', "%{$q}%")
                    ->orWhere('mobile_no', 'like', "%{$q}%")
                    ->orWhere('nid_number', 'like', "%{$q}%")
                    ->orWhere('patientcode', 'like', "%{$q}%");
            })
            ->orderByDesc('id')
            ->limit(15)
            ->get(['id', 'patientcode', 'patientname', 'mobile_no']);

        return response()->json($patients);
    }

    private function validateReport(Request $request): array
    {
        return $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['required', 'exists:doctors,id'],
            'report_date' => ['required', 'date'],
            'findings' => ['required', 'string'],
            'impression' => ['nullable', 'string'],
            'followup_date' => ['nullable', 'date', 'after_or_equal:report_date'],
        ]);
    }

    private function createFollowup(UsgReport $report, string $followupDate): void
    {
        $followup = UsgFollowup::create([
            'usg_report_id' => $report->id,
            'patient_id' => $report->patient_id,
            'doctor_id' => $report->doctor_id,
            'followup_date' => $followupDate,
            'status' => 'pending',
        ]);

        $this->createFollowupNotifications($followup);
    }

    private function createFollowupNotifications(UsgFollowup $followup): void
    {
        $patient = $followup->patient;
        $doctor = $followup->doctor;

        DoctorNotification::create([
            'doctor_id' => $followup->doctor_id,
            'usg_followup_id' => $followup->id,
            'type' => 'usg_followup',
            'title' => 'USG Follow-up: ' . ($patient->patientname ?? 'Patient'),
            'message' => 'Follow-up due on ' . $followup->followup_date->format('d M Y') .
                ' for ' . ($patient->patientname ?? 'Patient') .
                ' (Code: ' . ($patient->patientcode ?? '-') . ').' .
                ' Doctor: ' . ($doctor->doctor_name ?? '-') . '.',
            'is_read' => false,
            'notified_at' => now(),
        ]);
    }
}
