<?php

namespace App\Services;

use App\Models\PrescriptionPrint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * PrescriptionPrintService
 *
 * Builds and stores an immutable snapshot every time a prescription is printed.
 *
 * This service is intentionally focused on two things only:
 *   1. Capturing the data (medicines, patient, doctor, clinical) at print time.
 *   2. Persisting the snapshot + integrity hash into the prescription_prints table.
 *
 * PDF / HTML rendering is deliberately kept OUT of this class — the caller
 * (controller / view) is responsible for rendering the returned snapshots.
 */
class PrescriptionPrintService
{
    /**
     * Draft-order-type key used for each print order type when talking to
     * MedicineDraftService (the draft keys differ from the print keys).
     *
     * @var array<string, string>
     */
    protected const DRAFT_TYPE = [
        'admit' => 'admit',
        'pre'   => 'preorder',
        'post'  => 'postorder',
        'round' => 'round',
        'fresh' => 'fresh',
    ];

    /**
     * Fallback patient medicine table + order_type filter for each print order
     * type, used only when the session draft is empty.
     *
     * @var array<string, array{table: string, order: array<int, string>}>
     */
    protected const MEDICINE_TABLES = [
        'admit' => ['table' => 'patient_admission_medicines', 'order' => ['admit']],
        'pre'   => ['table' => 'patient_presurgery_medicines', 'order' => ['preorder']],
        'post'  => ['table' => 'patient_postsurgery_medicines', 'order' => ['postorder']],
        'round' => ['table' => 'patient_round_medicines', 'order' => ['postorder', 'round']],
        'fresh' => ['table' => 'patient_fresh_medicines', 'order' => ['fresh']],
    ];

    /** @var MedicineDraftService */
    protected MedicineDraftService $draftService;

    /**
     * @param  MedicineDraftService  $draftService
     */
    public function __construct(MedicineDraftService $draftService)
    {
        $this->draftService = $draftService;
    }

    /**
     * Build the four snapshots, generate an integrity hash and persist a record
     * in the prescription_prints table.
     *
     * @param  string  $orderType   admit|pre|post|round|fresh
     * @param  string  $patientcode
     * @param  string  $stageTitle  Optional stage title for prescription display
     * @return array{
     *     print_id: int,
     *     print_hash: string,
     *     snapshots: array<string, array>,
     *     admission: object|null,
     *     patient: object|null,
     *     medicines: array<int, mixed>
     * }
     *
     * @throws RuntimeException When no active admission exists for the patient.
     */
    public function generate(string $orderType, string $patientcode, string $stageTitle = ''): array
    {
        // 1. Resolve the active (current) admission for the patient.
        $admission = $this->getActiveAdmission($patientcode);

        if (!$admission) {
            throw new RuntimeException(
                "No active admission found for patient '{$patientcode}'."
            );
        }

        // 2. Load the patient.
        $patient = DB::table('patients')->where('patientcode', $patientcode)->first();

        // 3. Load the medicines (session draft first, then DB fallback).
        $medicines = $this->loadMedicines($orderType, $patientcode, $admission);

        // 4. Build the four snapshots.
        $snapshots = [
            'medicines_snapshot' => $this->buildMedicinesSnapshot($medicines),
            'patient_snapshot'   => $this->buildPatientSnapshot($patient),
            'doctor_snapshot'    => $this->buildDoctorSnapshot($admission),
            'clinical_snapshot'  => $this->buildClinicalSnapshot($admission, $patientcode, $patient->id ?? null),
        ];

        // 5. Generate a SHA-256 integrity hash from the combined snapshots.
        $printHash = $this->generateHash($snapshots);

        // 6. Persist the immutable print record.
        $stage = (int) ($admission->status ?? 1);
        $printedBy = Auth::id();

        $print = PrescriptionPrint::create([
            'patientcode'        => $patientcode,
            'admissionid'        => $admission->admissionid ?? '',
            'order_type'         => $orderType,
            'stage'              => $stage,
            'medicines_snapshot' => $snapshots['medicines_snapshot'],
            'patient_snapshot'   => $snapshots['patient_snapshot'],
            'doctor_snapshot'    => $snapshots['doctor_snapshot'],
            'clinical_snapshot'  => $snapshots['clinical_snapshot'],
            'print_hash'         => $printHash,
            'printed_by'         => $printedBy,
            'printed_at'         => now(),
            'ip_address'         => request()->ip(),
        ]);

        // 7. Return everything the caller needs to render the print.
        return [
            'print_id'   => $print->id,
            'print_hash' => $printHash,
            'snapshots'  => $snapshots,
            'admission'  => $admission,
            'patient'    => $patient,
            'medicines'  => $medicines,
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * Get the active (Active = 1) admission for a patient.
     */
    protected function getActiveAdmission(string $patientcode): ?object
    {
        if (!Schema::hasTable('patient_admission')) {
            return null;
        }

        return DB::table('patient_admission')
            ->where('patientcode', $patientcode)
            ->where('Active', 1)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Load medicines: use the session draft first, then fall back to the
     * pre-saved patient medicine table for the order type.
     */
    protected function loadMedicines(string $orderType, string $patientcode, object $admission): Collection
    {
        $admissionId = $admission->admissionid ?? '';
        $draftType   = self::DRAFT_TYPE[$orderType] ?? $orderType;

        $medicines = $this->draftService->list($draftType, $patientcode, $admissionId);

        if ($medicines->isNotEmpty()) {
            return $medicines;
        }

        return $this->fallbackMedicines($orderType, $patientcode, $admissionId);
    }

    /**
     * Read the pre-saved rows from the patient medicine table when the session
     * draft is empty.
     */
    protected function fallbackMedicines(string $orderType, string $patientcode, string $admissionId): Collection
    {
        $config = self::MEDICINE_TABLES[$orderType] ?? null;

        if (!$config || !Schema::hasTable($config['table'])) {
            return collect();
        }

        $query = DB::table($config['table'])
            ->where('admissionid', $admissionId)
            ->whereIn('order_type', $config['order'])
            ->orderBy('id', 'asc');

        // On-admission rows are keyed by patientcode; the remaining surgery/
        // round/fresh rows store the patient code in the patientid column.
        if ($orderType === 'admit') {
            $query->where('patientcode', $patientcode);
        } else {
            $query->where('patientid', $patientcode);
        }

        return $query->get();
    }

    /**
     * Normalise the medicine list into a serialisable array snapshot.
     *
     * @param  Collection<mixed, object|array>  $medicines
     */
    protected function buildMedicinesSnapshot(Collection $medicines): array
    {
        return $medicines
            ->map(function ($item) {
                $row = is_array($item) ? $item : (array) $item;

                return [
                    'group'       => $row['group']       ?? '',
                    'name'        => $row['name']        ?? '',
                    'strength'    => $row['strength']    ?? '',
                    'dose'        => $row['dose']        ?? '',
                    'morning'     => $row['morning']     ?? '',
                    'noon'        => $row['noon']        ?? '',
                    'night'       => $row['night']       ?? '',
                    'route'       => $row['route']       ?? '',
                    'frequency'   => $row['frequency']   ?? '',
                    'duration'    => $row['duration']    ?? '',
                    'timing'      => $row['timing']      ?? '',
                    'meal_timing' => $row['meal_timing'] ?? '',
                    'instruction' => $row['instruction'] ?? '',
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Normalise the patient row into a serialisable snapshot.
     */
    protected function buildPatientSnapshot(?object $patient): array
    {
        return [
            'patientcode'   => $patient->patientcode   ?? '',
            'patientname'   => $patient->patientname   ?? '',
            'address'       => $patient->address       ?? '',
            'age'           => $patient->age           ?? null,
            'date_of_birth' => $patient->date_of_birth ?? null,
            'mobile_no'     => $patient->mobile_no     ?? '',
            'contact_no'    => $patient->contact_no    ?? '',
            'gender'        => $patient->gender        ?? '',
            'blood_group'   => $patient->blood_group   ?? '',
            'nid_number'    => $patient->nid_number    ?? '',
        ];
    }

    /**
     * Resolve the attending doctor from the admission's doctor_id (or the
     * admission's inline doctor fields) and build a snapshot.
     */
    protected function buildDoctorSnapshot(object $admission): array
    {
        $doctor = null;

        if (!empty($admission->doctor_id) && Schema::hasTable('doctors')) {
            $doctor = DB::table('doctors')->where('id', $admission->doctor_id)->first();
        }

        return [
            'doctor_name' => $doctor->doctor_name ?? $admission->doctor_name ?? '',
            'speciality'  => $doctor->speciality  ?? $admission->department ?? '',
            'reg_no'      => $doctor->reg_no      ?? $admission->doctor_reg_no ?? '',
            'contact'     => $doctor->contact     ?? $admission->doctor_mobile ?? '',
            'posting'     => $doctor->Posting     ?? '',
        ];
    }

    /**
     * Build the clinical snapshot: chief complaints + investigations from the
     * admission template, and on-examination vitals.
     *
     * Vitals follow the same rule as the outdoor prescription flow
     * (PreconAssessmentReader): the core four (Pulse/BP/Temp/Weight) are ALWAYS
     * present — even when empty — so every print shows the same labels; any
     * extra vitals are appended. Nursing admissions is used as a gap filler.
     */
    protected function buildClinicalSnapshot(object $admission, string $patientcode, ?int $patientId = null): array
    {
        $templateId = $admission->templeteid ?? '';

        // Chief complaints.
        $cc = collect();
        if ($templateId && Schema::hasTable('template_complain')) {
            $cc = DB::table('template_complain')
                ->where('templateid', $templateId)
                ->get();
        }

        // Investigations.
        $investigations = collect();
        if ($templateId && Schema::hasTable('template_investigations')) {
            $investigations = DB::table('template_investigations')
                ->where('templateid', $templateId)
                ->get();
        }

        // On examination (vitals): shared Pre-Con Assessment reader first.
        $oe = ['Pulse' => '', 'BP' => '', 'Temp' => '', 'Weight' => ''];
        $extraOE = [];

        $readerText = app(PreconAssessmentReader::class)
            ->textFor(null, $patientcode, $patientId);
        if ($readerText !== '') {
            foreach (explode("\n", $readerText) as $oeLine) {
                $oeLine = trim($oeLine);
                if ($oeLine === '') {
                    continue;
                }
                $colon = strpos($oeLine, ':');
                if ($colon === false) {
                    $extraOE[] = $oeLine;
                    continue;
                }
                $label = trim(substr($oeLine, 0, $colon));
                $full  = trim(substr($oeLine, $colon + 1));
                $lower = mb_strtolower($label);
                if (str_contains($lower, 'pulse')) {
                    $oe['Pulse'] = $full;
                } elseif (str_contains($lower, 'bp') || str_contains($lower, 'blood')) {
                    $oe['BP'] = $full;
                } elseif (str_contains($lower, 'temp')) {
                    $oe['Temp'] = $full;
                } elseif (str_contains($lower, 'weight') || str_contains($lower, 'wt')) {
                    $oe['Weight'] = $full;
                } else {
                    $extraOE[] = $oeLine;
                }
            }
        }

        // Nursing fallback — fills only the still-empty core four.
        if (Schema::hasTable('nursing_admissions')) {
            $nursing = DB::table('nursing_admissions')
                ->where('patient_code', $patientcode)
                ->orderByDesc('id')
                ->first();

            if ($nursing) {
                if ($oe['Pulse'] === '')   $oe['Pulse']   = trim((string) ($nursing->pulse ?? ''));
                if ($oe['BP'] === '')      $oe['BP']      = trim((string) ($nursing->bp ?? ''));
                if ($oe['Temp'] === '')    $oe['Temp']    = trim((string) ($nursing->temp ?? $nursing->temperature ?? ''));
                if ($oe['Weight'] === '')  $oe['Weight']  = trim((string) ($nursing->weight ?? ''));
            }
        }

        return [
            'chief_complaints' => $cc->values()->all(),
            'investigations'   => $investigations->values()->all(),
            'on_examination'   => $oe + $extraOE,
        ];
    }

    /**
     * Generate a SHA-256 integrity hash from the combined snapshots.
     *
     * @param  array<string, array>  $snapshots
     */
    protected function generateHash(array $snapshots): string
    {
        return hash('sha256', json_encode($snapshots, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}