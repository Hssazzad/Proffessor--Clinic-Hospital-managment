<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    // Billing Overview
    public function index()
    {
        $payments = Payment::with('patient')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('patients.billing.index', compact('payments'));
    }

    // Add Payment form
    public function create()
    {
        return view('patients.billing.create');
    }

    // AJAX Patient Search
    public function searchPatient(Request $request)
    {
        $search = $request->get('q');
        $patients = Patient::where('patientname', 'LIKE', "%{$search}%")
            ->orWhere('patientcode', 'LIKE', "%{$search}%")
            ->orWhere('mobile_no', 'LIKE', "%{$search}%")
            ->orderBy('patientname')
            ->limit(20)
            ->get(['id', 'patientname', 'patientcode', 'mobile_no']);

        $results = $patients->map(function ($p) {
            return [
                'id'   => $p->id,
                'text' => $p->patientname . ' | ' . $p->patientcode . ' | ' . ($p->mobile_no ?? 'N/A'),
            ];
        });
        return response()->json(['results' => $results]);
    }

    // Payment store
    public function store(Request $request)
    {
        $request->validate([
            'patient_id'   => 'required|exists:patients,id',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount'  => 'required|numeric|min:0',
            'payment_date' => 'required|date',
        ]);

        $payable = $request->total_amount - ($request->discount ?? 0);
        $due     = $payable - $request->paid_amount;

        if ($due <= 0) {
            $status = 'Paid';
        } elseif ($request->paid_amount > 0) {
            $status = 'Partial';
        } else {
            $status = 'Due';
        }

        $invoice = 'INV-' . date('Ymd') . '-' . str_pad(
            Payment::whereDate('created_at', today())->count() + 1,
            4, '0', STR_PAD_LEFT
        );

        Payment::create([
            'invoice_no'      => $invoice,
            'patient_id'      => $request->patient_id,
            'prescription_id' => $request->prescription_id ?? null,
            'appointment_id'  => $request->appointment_id ?? null,
            'total_amount'    => $request->total_amount,
            'discount'        => $request->discount ?? 0,
            'payable_amount'  => $payable,
            'paid_amount'     => $request->paid_amount,
            'due_amount'      => max($due, 0),
            'refund_amount'   => $due < 0 ? abs($due) : 0,
            'payment_status'  => $status,
            'payment_date'    => $request->payment_date,
            'remarks'         => $request->remarks ?? null,
            'created_by'      => Auth::id(),
            'updated_by'      => Auth::id(),
        ]);

        return redirect()->route('patients.billing.index')
            ->with('success', 'Payment successfully added!');
    }

    // Edit form
    public function edit($id)
    {
        $payment = Payment::with('patient')->findOrFail($id);
        return view('patients.billing.edit', compact('payment'));
    }

    // Update
    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $request->validate([
            'total_amount' => 'required|numeric|min:0',
            'paid_amount'  => 'required|numeric|min:0',
            'payment_date' => 'required|date',
        ]);

        $payable = $request->total_amount - ($request->discount ?? 0);
        $due     = $payable - $request->paid_amount;

        if ($due <= 0) {
            $status = 'Paid';
        } elseif ($request->paid_amount > 0) {
            $status = 'Partial';
        } else {
            $status = 'Due';
        }

        $payment->update([
            'total_amount'   => $request->total_amount,
            'discount'       => $request->discount ?? 0,
            'payable_amount' => $payable,
            'paid_amount'    => $request->paid_amount,
            'due_amount'     => max($due, 0),
            'refund_amount'  => $due < 0 ? abs($due) : 0,
            'payment_status' => $status,
            'payment_date'   => $request->payment_date,
            'remarks'        => $request->remarks ?? null,
            'updated_by'     => Auth::id(),
        ]);

        return redirect()->route('patients.billing.index')
            ->with('success', 'Payment successfully updated!');
    }

    // Delete
    public function destroy($id)
    {
        Payment::findOrFail($id)->delete();
        return redirect()->route('patients.billing.index')
            ->with('success', 'Payment successfully deleted!');
    }

    // Due Payments list
    public function due()
    {
        $payments = Payment::with('patient')
            ->where('due_amount', '>', 0)
            ->orderBy('created_at', 'desc')
            ->get();
        return view('patients.billing.due', compact('payments'));
    }
}
?>