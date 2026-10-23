<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminPaymentController extends Controller
{
    public function pendingPayments()
    {
        $payments = DB::table('investigation_payments')
            ->where('approval_status', 'pending')
            ->orderByDesc('ID')
            ->get();

        $summary = [
            'pending'      => $payments->count(),
            'total_amount' => $payments->sum('TotalBill'),
            'total_due'    => $payments->sum('DueAmount'),
        ];

        return view('billing.admin_pending_payments', [
            'pendingPayments' => $payments,
            'summary'         => $summary,
        ]);
    }

    public function approvePayment($id)
    {
        DB::table('investigation_payments')
            ->where('ID', $id)
            ->update([
                'approval_status' => 'approved',
                'approved_by' => Auth::id(),
                'updated_at' => now()
            ]);

        return back()
            ->with('success', 'Payment approved successfully!')
            ->with('approved_id', $id);
    }

    public function rejectPayment($id)
    {
        DB::table('investigation_payments')
            ->where('ID', $id)
            ->update([
                'approval_status' => 'rejected',
                'approved_by' => Auth::id(),
                'updated_at' => now()
            ]);

        return back()->with('error', 'Payment request rejected.');
    }

    // ============================================================
    // AUDIT REPORT — daily view of all reference/discount cases
    // ============================================================
    public function auditReport(\Illuminate\Http\Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate   = $request->input('to_date');
        $status   = $request->input('status'); 

        $query = DB::table('investigation_payments as ip')
            ->leftJoin('users as u', 'u.id', '=', 'ip.approved_by')
            ->where(function ($query) {
                $query->whereNotNull('ip.reference_name')
                    ->where('ip.reference_name', '!=', '')
                    ->orWhere('ip.Discount', '>', 0);
            })
            ->when($fromDate, fn ($query) => $query->whereDate('ip.PaymentDate', '>=', $fromDate))
            ->when($toDate, fn ($query) => $query->whereDate('ip.PaymentDate', '<=', $toDate))
            ->select(
                'ip.ID',
                'ip.BillNo',
                'ip.PatientName',
                'ip.PatientCode',
                'ip.MobileNo',
                'ip.TotalBill',
                'ip.Discount',
                'ip.PaidAmount',
                'ip.DueAmount',
                'ip.reference_name',
                'ip.approval_status',
                'ip.CollectedBy',
                'ip.PaymentDate',
                'ip.updated_at',
                'u.name as ApprovedByName'
            )
            ->when($status, fn($q) => $q->where('ip.approval_status', $status))
            ->orderByDesc('ip.ID');

        $payments = $query->get();

        $summary = [
            'total_cases' => $payments->count(),
            'approved'    => $payments->where('approval_status', 'approved')->count(),
            'pending'     => $payments->where('approval_status', 'pending')->count(),
            'rejected'    => $payments->where('approval_status', 'rejected')->count(),
            'total_due'   => $payments->sum('DueAmount'),
        ];

        return view('billing.discount-report.admin_audit_report', compact('payments', 'summary', 'fromDate', 'toDate', 'status'));
    }
}
