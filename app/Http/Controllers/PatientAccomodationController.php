<?php
namespace App\Http\Controllers;

use App\Models\Bed;
use Illuminate\Http\Request;

class PatientAccomodationController extends Controller
{
    public function index()
    {
        $totalBeds       = Bed::count();
        $availableBeds   = Bed::where('status', 'vacant')->count();
        $occupiedBeds    = Bed::where('status', 'occupied')->count();
        $maintenanceBeds = Bed::where('status', 'maintenance')->count();
        $beds            = Bed::with('currentAdmission.patient')->get();

        return view('admission.patient_accomodation', compact(
            'totalBeds', 'availableBeds', 'occupiedBeds', 'maintenanceBeds', 'beds'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bed_number'    => 'required|string|unique:beds,bed_number',
            'bed_type'      => 'required|string|max:255',
            'price_per_day' => 'nullable|numeric|min:0',
        ]);

        $validated['status'] = 'vacant';
        Bed::create($validated);

        return response()->json(['success' => true, 'message' => 'বেড সফলভাবে যোগ করা হয়েছে।']);
    }

    /**
     * Bed Matrix-এর Details modal থেকে বেড পরিবর্তন করে।
     * পুরনো বেড vacant করে, নতুন বেড occupied করে।
     */
    public function changeBed(Request $request)
    {
        $request->validate([
            'admission_id' => 'required|exists:patient_admission,id',
            'new_bed_id'   => 'required|exists:beds,id',
        ]);

        $newBed = Bed::findOrFail($request->new_bed_id);

        if ($newBed->status !== 'vacant') {
            return redirect()
                ->route('admission.patient_accomodation')
                ->with('error', 'নির্বাচিত বেডটি এখন ফাঁকা নেই।');
        }

        // পুরনো বেড vacant করো
        $oldBed = Bed::where('current_admission_id', $request->admission_id)->first();
        if ($oldBed) {
            $oldBed->update([
                'status'               => 'vacant',
                'current_admission_id' => null,
            ]);
        }

        // নতুন বেড occupied করো
        $newBed->update([
            'status'               => 'occupied',
            'current_admission_id' => $request->admission_id,
        ]);

        return redirect()
            ->route('admission.patient_accomodation')
            ->with('success', 'বেড পরিবর্তন সফল হয়েছে।');
    }

    /**
     * বেডের বিস্তারিত — same page-এর modal-এ redirect করে।
     */
    public function bedDetails($bed)
    {
        return redirect()
            ->to(route('admission.patient_accomodation') . '#bed-detail-' . $bed);
    }

    /**
     * বেডের নোটস — same page-এর modal-এ redirect করে।
     */
    public function notes($bed)
    {
        return redirect()
            ->to(route('admission.patient_accomodation') . '#bed-detail-' . $bed);
    }

    /**
     * বেডের স্ট্যাটাস আপডেট ফর্ম — same page-এর modal-এ redirect করে।
     */
    public function updateStatusForm($bed)
    {
        return redirect()
            ->to(route('admission.patient_accomodation') . '#bed-detail-' . $bed);
    }
}