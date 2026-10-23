<?php

namespace App\Services\Billing;

use App\Models\PatientDoctorVisit;
use Illuminate\Support\Facades\DB;

class PatientDoctorVisitService
{
    public function record(
        int $patientId,
        int $doctorId,
        int $invoiceId,
        int $chamberFeeConfigId,
        string $visitType,
        float $consultationFee,
        ?string $visitDate = null
    ): PatientDoctorVisit {
        $visitTypeId = DB::table('visit_types')
            ->where('visit_type_name', $visitType)
            ->value('id');

        $visit = PatientDoctorVisit::create([
            'patient_id'            => $patientId,
            'doctor_id'             => $doctorId,
            'invoice_id'            => $invoiceId,
            'chamber_fee_config_id' => $chamberFeeConfigId,
            'visit_type_id'         => $visitTypeId ?: null,
            'consultation_fee'      => $consultationFee,
            'visit_date'            => $visitDate ?: now(),
        ]);

        return $visit;
    }

    public function recordMany(array $visits): void
    {
        foreach ($visits as $visit) {
            $this->record(
                $visit['patient_id'],
                $visit['doctor_id'],
                $visit['invoice_id'],
                $visit['chamber_fee_config_id'],
                $visit['visit_type'],
                $visit['consultation_fee'],
                $visit['visit_date'] ?? null,
            );
        }
    }
}
