<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FollowupDueController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('followup_dues')
            ->leftJoin('patients', 'followup_dues.patientcode', '=', 'patients.patientcode')
            ->leftJoin('procedure_types', 'followup_dues.procedure_type_id', '=', 'procedure_types.id')
            ->select(
                'followup_dues.*',
                'patients.patientname',
                'patients.mobile_no',
                'procedure_types.procedure_name'
            );

        if ($request->filled('status')) {
            if ($request->status === 'paid') {
                $query->where('followup_dues.status', 'paid');
            } elseif ($request->status === 'waived') {
                $query->where('followup_dues.status', 'pending')
                    ->whereDate('followup_dues.due_deadline', '<', now()->toDateString());
            } elseif ($request->status === 'pending') {
                $query->where('followup_dues.status', 'pending')
                    ->whereDate('followup_dues.due_deadline', '>=', now()->toDateString());
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('followup_dues.admission_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('followup_dues.admission_date', '<=', $request->date_to);
        }

        $dues = $query->orderBy('followup_dues.due_deadline', 'asc')->paginate(30);

        return view('followupdues.index', compact('dues'));
    }

    public function markPaid(Request $request, $id)
    {
        $request->validate([
            'paid_amount' => 'required|numeric|min:0.01',
        ]);

        $due = DB::table('followup_dues')->where('id', $id)->first();

        if (!$due) {
            return redirect()->back()->with('error', 'Record not found.');
        }

        if ($due->status === 'paid') {
            return redirect()->back()->with('error', 'Already marked as paid.');
        }

        DB::beginTransaction();
        try {
            DB::table('followup_dues')
                ->where('id', $id)
                ->update([
                    'status'       => 'paid',
                    'paid_amount'  => $request->paid_amount,
                    'resolved_at'  => now(),
                    'resolved_by'  => auth()->id(),
                    'updated_at'   => now(),
                ]);

            DB::commit();

            return redirect()->back()->with('success', 'Follow-up due marked as paid successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update: ' . $e->getMessage());
        }
    }
}
