<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\ConfigDistrict;
use Illuminate\Support\Facades\File;

class PatientController extends Controller
{
    /**
     * Display patient list with search
     */
    public function index(Request $request)
    {
        $q = $request->get('q');
        $patients = Patient::query()
            ->when($q, function ($query) use ($q) {
                $query->where('patientname', 'like', "%{$q}%")
                      ->orWhere('patientcode', 'like', "%{$q}%")
                      ->orWhere('mobile_no', 'like', "%{$q}%")
                      ->orWhere('nid_number', 'like', "%{$q}%");
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('patients.create', compact('patients', 'q'));
    }

    /**
     * Show new patient form with auto-generated code
     */
    public function newpatient()
    {
        $districts = DB::table('configdistrict')
            ->select('code', 'name')
            ->orderBy('name')
            ->get();

        $yy = (int) Carbon::now()->format('y');

        $newCode = DB::transaction(function () use ($yy) {
            $lastCode = DB::table('configpatientcode')
                ->whereBetween('Code', [$yy * 100000, $yy * 100000 + 999])
                ->lockForUpdate()
                ->max('Code');

            $nextSerial = $lastCode ? (($lastCode % 100000) + 1) : 1;

            if ($nextSerial > 999) {
                abort(500, 'Yearly patient code limit reached (999).');
            }

            $code = ($yy * 100000) + $nextSerial;

            return $code;
        });

        $patients = Patient::orderByDesc('id')->paginate(15);

        return view('patients.create', compact('newCode', 'districts', 'patients'));
    }

    /**
     * Fetch reference persons by type (AJAX)
     */
    public function fetch_reference_person(Request $request)
    {
        $type = $request->reference_type;

        $rows = DB::table('patient_ref')
            ->select('ID', 'Name', 'Mobile')
            ->where('active', 1)
            ->where('ref_type', $type)
            ->orderBy('Name')
            ->get();

        $html = '<option value="">-- Select Person --</option>';
        foreach ($rows as $r) {
            $html .= '<option value="'.$r->ID.'">'.$r->Name.' ('.$r->Mobile.')</option>';
        }

        return response($html);
    }

    /**
     * Store new reference person (AJAX)
     */
    public function store_reference_person(Request $request)
    {
        $data = $request->validate([
            'ref_type' => ['required', Rule::in(['OfficeEmployee','PCNurse','MidWife','Others'])],
            'Name'     => ['required', 'string', 'max:25'],
            'Mobile'   => ['nullable', 'string', 'max:15'],
        ]);

        $code = strtoupper(substr($data['ref_type'], 0, 2)) . str_pad((string)rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $id = DB::table('patient_ref')->insertGetId([
            'Code'    => $code,
            'ref_type'=> $data['ref_type'],
            'Name'    => $data['Name'],
            'Mobile'  => $data['Mobile'] ?? '',
            'active'  => 1,
        ]);

        return response()->json([
            'ok' => true,
            'id' => $id,
            'type' => $data['ref_type'],
        ]);
    }

    /**
     * Get all reference persons (unused, kept for compatibility)
     */
    public function refPersons(Request $request)
    {
        $rows = DB::table('patient_ref')
            ->select('ID as id', 'Name as name', 'Mobile as mobile')
            ->orderBy('Name')
            ->get();

        return response()->json($rows);
    }

    /**
     * Store new patient
     * After save → redirect to Billing Payment page for this patient
     */
    public function storepatientdata(Request $request)
    {
        $validated = $request->validate([
            // REQUIRED
            'registration_date' => ['nullable', 'date'],
            'patientname'    => ['required', 'string', 'max:150'],
            'mobile_no'      => ['required', 'regex:/^01[0-9]{9}$/'],
            'age'            => ['required', 'string', 'min:1', 'max:150'],
            'gender'         => ['required', Rule::in(['Male', 'Female', 'Other'])],

            // OPTIONAL
            'patientfather'  => ['nullable', 'string', 'max:150'],
            'patienthusband' => ['nullable', 'string', 'max:70'],
            'photo'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],

            // ADDRESS
            'district'       => ['nullable', 'string', 'max:20'],
            'upozila'        => ['nullable', 'string', 'max:20'],
            'union'          => ['nullable', 'string', 'max:20'],
            'village'        => ['nullable', 'string', 'max:20'],
            'post'           => ['nullable', 'string', 'max:20'],
            'address'        => ['nullable', 'string', 'max:255'],

            // CONTACT
            'spomobile_no'   => ['nullable', 'regex:/^01[0-9]{9}$/'],
            'relmobile_no'   => ['nullable', 'regex:/^01[0-9]{9}$/'],
            'nid_number'     => ['nullable', 'digits_between:10,17'],
            'email'          => ['nullable', 'email', 'max:120'],

            // HEALTH
            'blood_group'    => ['nullable', 'string', 'max:5'],
            'notes'          => ['nullable', 'string'],

            // REFERENCE
            'reference_type'   => ['nullable', Rule::in(['Self', 'OfficeEmployee', 'PCNurse', 'MidWife', 'NOCOM', 'Others'])],
            'reference_person' => ['nullable', 'integer'],
            'reference_name'   => ['nullable', 'string', 'max:120'],

            // CAMERA
            'camera_image'   => ['nullable', 'string'],
        ]);

        // ===== Reference Type Validation =====
        $type = $validated['reference_type'] ?? null;

        if ($type === 'Self' || $type === 'Others') {
            if (empty(trim((string) ($validated['reference_name'] ?? '')))) {
                return back()->withErrors([
                    'reference_name' => 'Reference Name is required for Self/Others.'
                ])->withInput();
            }
            $validated['reference_person'] = null;

        } elseif (in_array($type, ['OfficeEmployee', 'PCNurse', 'MidWife'], true)) {

            if (empty($validated['reference_person'])) {
                return back()->withErrors([
                    'reference_person' => 'Please select Reference Person.'
                ])->withInput();
            }

            $ref = DB::table('patient_ref')
                ->select('ID', 'Name', 'Mobile', 'ref_type', 'active')
                ->where('ID', $validated['reference_person'])
                ->where('active', 1)
                ->first();

            if (!$ref) {
                return back()->withErrors([
                    'reference_person' => 'Invalid Reference Person selected.'
                ])->withInput();
            }

            if (!empty($ref->ref_type) && $ref->ref_type !== $type) {
                return back()->withErrors([
                    'reference_person' => 'Reference Person type mismatch.'
                ])->withInput();
            }

            $validated['reference_name'] = $ref->Name;

        } else {
            $validated['reference_person'] = null;
            $validated['reference_name'] = null;
        }

        // ===== Photo Save =====
        $destinationPath = public_path('uploads/photos');

        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        if ($request->filled('camera_image')) {

            $image = $request->camera_image;

            if (preg_match('/^data:image\/(\w+);base64,/', $image, $matches)) {
                $image = substr($image, strpos($image, ',') + 1);
                $ext = strtolower($matches[1]);
            } else {
                return back()->withErrors([
                    'photo' => 'Invalid camera image data'
                ])->withInput();
            }

            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                return back()->withErrors([
                    'photo' => 'Unsupported camera image type'
                ])->withInput();
            }

            $image = str_replace(' ', '+', $image);
            $imageData = base64_decode($image);

            if ($imageData === false) {
                return back()->withErrors([
                    'photo' => 'Camera image decode failed'
                ])->withInput();
            }

            $imageName = 'cam_' . time() . '.' . $ext;
            $savePath = $destinationPath . '/' . $imageName;

            file_put_contents($savePath, $imageData);

            $validated['photo'] = $imageName;

        } elseif ($request->hasFile('photo')) {

            $file = $request->file('photo');
            $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $file->move($destinationPath, $filename);

            $validated['photo'] = $filename;
        }

        unset($validated['camera_image']);

        // ===== Generate Patient Code =====
        $yy = (int) Carbon::now()->format('y');

        $patientCode = DB::transaction(function () use ($yy) {
            $lastCode = DB::table('configpatientcode')
                ->whereBetween('Code', [$yy * 100000, $yy * 100000 + 999])
                ->lockForUpdate()
                ->max('Code');

            $nextSerial = $lastCode ? (($lastCode % 100000) + 1) : 1;

            if ($nextSerial > 999) {
                abort(500, 'Yearly patient code limit reached (999).');
            }

            $code = ($yy * 100000) + $nextSerial;

            DB::table('configpatientcode')->insert([
                'Code'            => $code,
                'LastCreatedDate' => Carbon::today(),
            ]);

            return $code;
        });

        $validated['patientcode'] = $patientCode;
        $validated['registration_date'] = $request->registration_date ?? now();
        $validated['post'] = $request->filled('post') ? $request->post : '';

        // ===== Save Patient =====
        $patient = Patient::create($validated);

        // ✅ Redirect to Billing Payment page for this patient
     return redirect()
      ->route('billing.invoice.index')
       ->with('success', 'Patient saved! Please complete payment.');
    }

    /**
     * Search patients
     */
    public function searchpatient(Request $request)
    {
        $query = trim($request->get('q'));

        $patients = Patient::query()
            ->when($query, function ($q) use ($query) {
                $q->where('patientname', 'like', "%{$query}%")
                  ->orWhere('mobile_no', 'like', "%{$query}%")
                  ->orWhere('nid_number', 'like', "%{$query}%")
                  ->orWhere('relmobile_no', 'like', "%{$query}%")
                  ->orWhere('date_of_birth', 'like', "%{$query}%")
                  ->orWhere('patientcode', 'like', "%{$query}%")
                  ->orWhere('notes', 'like', "%{$query}%");
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('patients.search', compact('patients', 'query'));
    }

    /**
     * Patient Audit / Visit Report — READ-ONLY.
     *
     * One row = one patient registration (patients table, no joins, so the
     * total count always matches the listed rows). Date filter uses a
     * start-of-day/end-of-day range on registration_date so DATETIME values
     * like 2025-09-12 23:59:10 are correctly included.
     */
    public function patientreport(Request $request)
    {
        $filters = $request->validate([
            'from_date' => 'nullable|date',
            'to_date'   => 'nullable|date|after_or_equal:from_date',
            'q'         => 'nullable|string|max:100',
        ]);

        $today = Carbon::today()->toDateString();
        $from  = $filters['from_date'] ?? $today;
        $to    = $filters['to_date'] ?? $today;
        $q     = trim($filters['q'] ?? '');

        $total = (clone $this->patientReportQuery($from, $to, $q))->count();

        $patients = $this->patientReportQuery($from, $to, $q)
            ->orderBy('registration_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(50)
            ->withQueryString();

        return view('patients.patientreport', compact('patients', 'total', 'from', 'to', 'q'));
    }

    /**
     * CSV export of the Patient Audit Report — READ-ONLY, respects filters.
     */
    public function patientreportExport(Request $request)
    {
        $filters = $request->validate([
            'from_date' => 'nullable|date',
            'to_date'   => 'nullable|date|after_or_equal:from_date',
            'q'         => 'nullable|string|max:100',
        ]);

        $today = Carbon::today()->toDateString();
        $from  = $filters['from_date'] ?? $today;
        $to    = $filters['to_date'] ?? $today;
        $q     = trim($filters['q'] ?? '');
        $filename = 'patient_audit_' . str_replace('-', '', $from) . '_' . str_replace('-', '', $to) . '.csv';

        return response()->streamDownload(function () use ($from, $to, $q) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($out, ['SL', 'Patient ID', 'Patient Name', 'Age', 'Gender', 'Phone', 'Visit Date', 'Visit Time']);
            $sl = 0;
            $this->patientReportQuery($from, $to, $q)
                ->orderBy('id')
                ->chunk(500, function ($rows) use ($out, &$sl) {
                    foreach ($rows as $p) {
                        $sl++;
                        $dt = $p->registration_date ? Carbon::parse($p->registration_date) : null;
                        fputcsv($out, [
                            $sl, $p->patientcode, $p->patientname, $p->age,
                            $p->gender, $p->mobile_no,
                            $dt ? $dt->format('d/m/Y') : '', $dt ? $dt->format('h:i A') : '',
                        ]);
                    }
                });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Shared read-only base query for the audit report (SELECT only).
     */
    private function patientReportQuery(string $from, string $to, string $q)
    {
        $start = Carbon::parse($from)->startOfDay()->toDateTimeString();
        $end   = Carbon::parse($to)->endOfDay()->toDateTimeString();

        return Patient::query()
            ->select(['id', 'patientcode', 'patientname', 'age', 'gender', 'mobile_no', 'registration_date'])
            ->whereBetween('registration_date', [$start, $end])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('patientcode', 'like', "%{$q}%")
                      ->orWhere('patientname', 'like', "%{$q}%")
                      ->orWhere('mobile_no', 'like', "%{$q}%");
                });
            });
    }

    /**
     * Show single patient
     */
    public function show(Patient $patient)
    {
        return view('patients.show', compact('patient'));
    }

    /**
     * Show edit patient form
     */
    public function editpatient($id = null)
    {
        if (!$id) {
            abort(404, 'Patient ID missing');
        }

        $patient = Patient::findOrFail($id);

        $districts = DB::table('configdistrict')
            ->orderBy('name')
            ->get(['code', 'name']);

        return view('patients.editpatient', compact('patient', 'districts'));
    }

    /**
     * Update patient
     */
    public function updatepatient(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);

        $request->validate([
            'registration_date' => 'nullable|date',
            'patientname'     => 'required|string|max:255',
            'mobile_no'       => ['required', 'regex:/^01[0-9]{9}$/'],
            'spomobile_no'    => ['nullable', 'regex:/^01[0-9]{9}$/'],
            'relmobile_no'    => ['nullable', 'regex:/^01[0-9]{9}$/'],
            'email'           => 'nullable|email|max:255',
            'date_of_birth'   => 'nullable|date',
            'age'             => 'required|string|max:100',
            'gender'          => 'required|in:Male,Female,Other',
            'blood_group'     => 'nullable|string|max:10',
            'nid_number'      => 'nullable|string|max:50',
            'patientfather'   => 'nullable|string|max:255',
            'patienthusband'  => 'nullable|string|max:255',
            'district'        => 'nullable|string|max:50',
            'upozila'         => 'nullable|string|max:50',
            'union'           => 'nullable|string|max:50',
            'village'         => 'nullable|string|max:50',
            'post'            => 'nullable|string|max:50',
            'reference_type'  => 'nullable|string|max:50',
            'reference_person'=> 'nullable|string|max:50',
            'reference_name'  => 'nullable|string|max:255',
            'notes'           => 'nullable|string',
            'photo'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = [
            'registration_date'=> $request->registration_date,
            'patientname'      => $request->patientname,
            'patientfather'    => $request->patientfather,
            'patienthusband'   => $request->patienthusband,
            'mobile_no'        => $request->mobile_no,
            'spomobile_no'     => $request->spomobile_no,
            'relmobile_no'     => $request->relmobile_no,
            'email'            => $request->email,
            'date_of_birth'    => $request->date_of_birth,
            'age'              => $request->age,
            'nid_number'       => $request->nid_number,
            'gender'           => $request->gender,
            'blood_group'      => $request->blood_group,
            'district'         => $request->district,
            'upozila'          => $request->upozila,
            'union'            => $request->union,
            'village'          => $request->village,
            'post'             => $request->filled('post') ? $request->post : '',
            'reference_type'   => $request->reference_type,
            'reference_person' => $request->reference_person,
            'reference_name'   => $request->reference_name,
            'notes'            => $request->notes,
        ];

        $destinationPath = public_path('uploads/photos');

        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        if ($request->filled('camera_image')) {

            $image = $request->camera_image;

            if (preg_match('/^data:image\/(\w+);base64,/', $image, $matches)) {
                $image = substr($image, strpos($image, ',') + 1);
                $ext = strtolower($matches[1]);
            } else {
                return back()->withErrors(['photo' => 'Invalid camera image data'])->withInput();
            }

            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                return back()->withErrors(['photo' => 'Unsupported camera image type'])->withInput();
            }

            $image = str_replace(' ', '+', $image);
            $imageData = base64_decode($image);

            if ($imageData === false) {
                return back()->withErrors(['photo' => 'Camera image decode failed'])->withInput();
            }

            $imageName = 'cam_' . time() . '.' . $ext;
            $savePath = $destinationPath . '/' . $imageName;

            file_put_contents($savePath, $imageData);

            if (!empty($patient->photo)) {
                $oldPath = public_path('uploads/photos/' . $patient->photo);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $data['photo'] = $imageName;

        } elseif ($request->hasFile('photo')) {

            $file = $request->file('photo');
            $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $file->move($destinationPath, $filename);

            if (!empty($patient->photo)) {
                $oldPath = public_path('uploads/photos/' . $patient->photo);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $data['photo'] = $filename;
        }

        $patient->update($data);

        return redirect()->route('patients.searchpatient')
            ->with('success', 'Patient updated successfully.');
    }

    /**
     * Delete patient
     */
    public function destroy(Request $request, Patient $patient)
    {
        $patient->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Patient deleted successfully!']);
        }

        return redirect()
            ->route('patients.searchpatient')
            ->with('success', 'Patient deleted.');
    }

    /**
     * Fetch admission history for patient (AJAX)
     */
    public function admissionHistory($patientId)
    {
        $admissions = DB::table('nursing_admissions')
            ->where('patient_id', $patientId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success'    => true,
            'admissions' => $admissions,
        ]);
    }
}
