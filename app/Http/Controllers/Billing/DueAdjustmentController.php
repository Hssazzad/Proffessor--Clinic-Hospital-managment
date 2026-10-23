<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\DueAdjustmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DueAdjustmentController extends Controller
{
    public function __construct(protected DueAdjustmentService $service)
    {
    }

    // GET /Billing/DueAdjustment — audit / approval page
    public function index(Request $request)
    {
        $status   = $request->input('status');
        $type     = $request->input('type');
        $fromDate = $request->input('from_date');
        $toDate   = $request->input('to_date');

        $query = DB::table('invoice_due_adjustments as ada')
            ->leftJoin('investigation_payments as ip', 'ip.ID', '=', 'ada.invoice_id')
            ->leftJoin('patients as p', 'p.id', '=', 'ip.PatientId')
            ->leftJoin('users as cu', 'cu.id', '=', 'ada.created_by')
            ->leftJoin('users as au', 'au.id', '=', 'ada.approved_by')
            ->select(
                'ada.*',
                'ip.BillNo',
                'ip.PatientName',
                'ip.PatientCode',
                'p.patientname as patient_name',
                'cu.name as created_by_name',
                'au.name as approved_by_name'
            )
            ->when($status, fn ($q) => $q->where('ada.approval_status', $status))
            ->when($type, fn ($q) => $q->where('ada.type', $type))
            ->when($fromDate, fn ($q) => $q->whereDate('ada.created_at', '>=', $fromDate))
            ->when($toDate, fn ($q) => $q->whereDate('ada.created_at', '<=', $toDate))
            ->orderByDesc('ada.id');

        $adjustments = $query->paginate(30)->withQueryString();

        $summary = [
            'total'    => DB::table('invoice_due_adjustments')->count(),
            'pending'  => DB::table('invoice_due_adjustments')->where('approval_status', 'pending')->count(),
            'approved' => DB::table('invoice_due_adjustments')->where('approval_status', 'approved')->sum('adjustment_amount'),
            'reversed' => DB::table('invoice_due_adjustments')->where('approval_status', 'reversed')->count(),
        ];

        return view('billing.due_adjustments.index', compact('adjustments', 'summary', 'status', 'type', 'fromDate', 'toDate'));
    }

    // POST /Billing/DueAdjustment/store
    public function store(Request $request)
    {
        $request->validate([
            'invoice_id'        => 'required|integer|min:1',
            'adjustment_amount' => 'required|numeric|min:0.01',
            'reason'            => 'required|string|max:255',
            'type'              => 'sometimes|in:adjustment,discount',
        ]);

        try {
            $result = $this->service->create(
                (int) $request->invoice_id,
                (float) $request->adjustment_amount,
                trim($request->reason),
                (int) auth()->id(),
                $this->isAdmin(),
                $request->input('type', 'adjustment')
            );

            if ($result['applied']) {
                DuelistFinalController::clearSummaryCache();
            }

            return response()->json([
                'success'         => true,
                'id'              => $result['id'],
                'approval_status' => $result['approval_status'],
                'applied'         => $result['applied'],
                'type'            => $result['type'],
                'message'         => $result['applied']
                    ? ($result['type'] === 'discount'
                        ? 'Discount applied to the invoice.'
                        : 'Due adjusted successfully.')
                    : ($result['type'] === 'discount'
                        ? 'Discount request submitted for admin approval.'
                        : 'Adjustment request submitted for admin approval.'),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            \Log::error('DueAdjustmentController@store: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to save adjustment.'], 500);
        }
    }

    // POST /Billing/DueAdjustment/approve/{id}
    public function approve($id)
    {
        $this->adminOnly();

        try {
            $result = $this->service->approve((int) $id, (int) auth()->id());
            DuelistFinalController::clearSummaryCache();

            return back()->with('success', 'Adjustment approved and applied to the invoice.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            \Log::error('DueAdjustmentController@approve: ' . $e->getMessage());
            return back()->with('error', 'Failed to approve adjustment.');
        }
    }

    // POST /Billing/DueAdjustment/reject/{id}
    public function reject($id)
    {
        $this->adminOnly();

        try {
            $this->service->reject((int) $id);
            return back()->with('success', 'Adjustment request rejected.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            \Log::error('DueAdjustmentController@reject: ' . $e->getMessage());
            return back()->with('error', 'Failed to reject adjustment.');
        }
    }

    // POST /Billing/DueAdjustment/reverse/{id}
    public function reverse(Request $request, $id)
    {
        $this->adminOnly();

        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            $this->service->reverse((int) $id, (int) auth()->id(), trim((string) $request->reason));
            DuelistFinalController::clearSummaryCache();

            return back()->with('success', 'Adjustment reversed. Due amount restored.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            \Log::error('DueAdjustmentController@reverse: ' . $e->getMessage());
            return back()->with('error', 'Failed to reverse adjustment.');
        }
    }

    private function isAdmin(): bool
    {
        return (int) (auth()->user()->accounttype ?? 0) === 99;
    }

    private function adminOnly(): void
    {
        if (!$this->isAdmin()) {
            abort(403, 'Admin access required.');
        }
    }
}
