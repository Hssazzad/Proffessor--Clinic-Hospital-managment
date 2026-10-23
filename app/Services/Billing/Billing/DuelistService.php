<?php

namespace App\Services\Billing;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DuelistService
{
    // ?. Due List ?? ????
    public function getDueList($perPage, $q, $showAll)
    {
        $query = DB::table('investigation_payments as ip')
            ->join('patients as p', 'p.id', '=', 'ip.PatientId')
            ->select(
                'p.id as patient_id',
                'p.patientname as PatientName',
                'p.patientcode as PatientCode',
                'p.mobile_no as MobileNo',
                DB::raw('GROUP_CONCAT(ip.BillNo ORDER BY ip.ID SEPARATOR ", ") as BillNo'),
                DB::raw('MAX(ip.PaymentDate) as PaymentDate'),
                DB::raw('SUM(ip.TotalBill) as TotalBill'),
                DB::raw('SUM(IFNULL(ip.Discount,0)) as Discount'),
                DB::raw('SUM(IFNULL(ip.PaidAmount,0)) as PaidAmount'),
                DB::raw('SUM(IFNULL(ip.DueAmount,0)) as DueAmount'),
                DB::raw('CASE
                    WHEN SUM(IFNULL(ip.DueAmount,0)) <= 0 THEN "paid"
                    WHEN SUM(IFNULL(ip.PaidAmount,0)) > 0 THEN "partial"
                    ELSE "due"
                 END as Status')
            )
            ->groupBy('p.id', 'p.patientname', 'p.patientcode', 'p.mobile_no');

        if (!$showAll) {
            $query->having('DueAmount', '>', 0);
        }

        if ($q !== '') {
            $like = "%{$q}%";
            $query->where(function ($qb) use ($like) {
                $qb->where('p.patientname',  'like', $like)
                   ->orWhere('p.patientcode', 'like', $like)
                   ->orWhere('p.mobile_no',   'like', $like)
                   ->orWhere('ip.BillNo',     'like', $like);
            });
        }

        return $query->orderByDesc('DueAmount')->paginate($perPage);
    }

    // ?. Summary ?? ????
    public function getSummaryData()
    {
        return Cache::remember('duelist_summary', 300, function () {
            return DB::table('investigation_payments as ip')
                ->join('patients as p', 'p.id', '=', 'ip.PatientId')
                ->selectRaw('
                    COUNT(DISTINCT p.id)         AS total_patients,
                    SUM(IFNULL(ip.DueAmount, 0)) AS total_due
                ')
                ->having(DB::raw('SUM(IFNULL(ip.DueAmount,0))'), '>', 0)
                ->first();
        });
    }

    // ?. Patient Details ?? ????
    public function getPatientDetailsData($patientId)
    {
        $patient = DB::table('patients')->where('id', $patientId)->first();

        if (!$patient) {
            return null; // ??????? ?? ???? null ??????
        }

        $rows = DB::table('investigation_payments')
            ->select('ID', 'BillNo', 'TotalBill', 'Discount', 'PaidAmount', 'DueAmount', 'PaymentDate', 'CollectedBy')
            ->where('PatientId', $patientId)
            ->orderByDesc('ID')
            ->get();

        $bills = $rows->map(function ($r) {
            return [
                'ID'         => $r->ID,
                'BillNo'     => $r->BillNo,
                'TotalBill'  => $r->TotalBill,
                'Discount'   => $r->Discount,
                'PaidAmount' => $r->PaidAmount,
                'DueAmount'  => $r->DueAmount,
            ];
        });

        $payments = $rows->filter(fn($r) => $r->PaidAmount > 0)
            ->map(function ($r) {
                return [
                    'ID'          => $r->ID,
                    'BillNo'      => $r->BillNo,
                    'PaymentDate' => $r->PaymentDate,
                    'amount'      => $r->PaidAmount,
                    'CollectedBy' => $r->CollectedBy,
                ];
            })
            ->values();

        return [
            'bills'    => $bills,
            'payments' => $payments,
        ];
    }
}