<?php

namespace App\Services\Billing;

use App\Models\ChamberFeeConfig;
use App\Models\PatientDoctorVisit;
use Carbon\Carbon;

class ConsultationFeeService
{
    public function calculate(int $patientId, int $doctorId, int $chamberFeeConfigId): array
    {
        $config = ChamberFeeConfig::find($chamberFeeConfigId);

        if (!$config) {
            return [
                'success' => false,
                'message' => 'Chamber fee configuration not found.',
            ];
        }

        $lastVisit = PatientDoctorVisit::where('patient_id', $patientId)
            ->where('doctor_id', $doctorId)
            ->orderByDesc('visit_date')
            ->first();

        if (!$lastVisit) {
            return $this->result('First', (float) $config->first_visit_fee, null, null);
        }

        $days = (int) Carbon::parse($lastVisit->visit_date)->diffInDays(now());

        if ($days <= (int) $config->followup_days) {
            return $this->result(
                'Follow-up',
                (float) $config->followup_fee,
                $days,
                $lastVisit->visit_date
            );
        }

        return $this->result(
            'Expired',
            (float) $config->expired_fee,
            $days,
            $lastVisit->visit_date
        );
    }

    private function result(string $visitType, float $fee, ?int $days, mixed $lastVisitDate): array
    {
        return [
            'success'         => true,
            'visit_type'      => $visitType,
            'fee'             => $fee,
            'days'            => $days,
            'last_visit_date' => $lastVisitDate ? Carbon::parse($lastVisitDate)->format('Y-m-d') : null,
        ];
    }
}
