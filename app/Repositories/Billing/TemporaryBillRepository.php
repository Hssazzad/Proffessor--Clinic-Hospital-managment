<?php
namespace App\Repositories\Billing;
use App\Repositories\Billing\Contracts\TemporaryBillRepositoryInterface;
use Illuminate\Support\Facades\DB;
class TemporaryBillRepository implements TemporaryBillRepositoryInterface
{
    public function create(array $data): int
    {
        return DB::table('tbl_bill_tmp')->insertGetId([
            'PatientCode' => (int) ($data['patient_code'] ?? 0),
            'MainCode'    => (int) ($data['main_code'] ?? 0),
            'SubCode'     => (int) ($data['sub_code'] ?? 0),
            'DoctorId'    => !empty($data['doctor_id']) ? (int) $data['doctor_id'] : null,
            'Name'        => $data['name'] ?? $data['sub_name'] ?? 'Unnamed Item',
            'Amount'      => (int) ($data['amount'] ?? 0),
            'chamber_fee_config_id' => !empty($data['chamber_fee_config_id'])
                ? (int) $data['chamber_fee_config_id'] : null,
            'BillNo'      => 0,
            'InputerID'   => auth()->id() ?? 0,
            'Status'      => 1,
        ]);
    }
    public function findByPatientCode(?string $patientCode): array
    {
        if (empty($patientCode)) {
            return [];
        }
        return DB::table('tbl_bill_tmp')
            ->where('PatientCode', $patientCode)
            ->get()
            ->toArray();
    }
    public function deleteById(int $tmpId): bool
    {
        return DB::table('tbl_bill_tmp')
            ->where('ID', $tmpId)
            ->delete() > 0;
    }
    public function deleteByPatientCode(?string $patientCode): bool
    {
        if (empty($patientCode)) {
            return false;
        }
        return DB::table('tbl_bill_tmp')
            ->where('PatientCode', $patientCode)
            ->delete() > 0;
    }
}