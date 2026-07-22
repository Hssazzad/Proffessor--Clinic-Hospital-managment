<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'date'   => ['nullable', 'date'],
            'status' => ['nullable', 'in:pending,approved,completed'],
        ]);

        $query = DB::table('appointments')
            ->leftJoin('patients', 'appointments.patient_id', '=', 'patients.id')
            ->select(
                'appointments.*',
                'patients.patientname',
                'patients.patientcode',
                'patients.mobile_no'
            );

        if ($request->filled('date')) {
            $query->whereDate('appointments.appointment_date', $request->date);
        }

        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->where(function ($statusQuery) {
                    $statusQuery->whereNull('appointments.status')
                        ->orWhere('appointments.status', 'pending');
                });
            } else {
                $query->where('appointments.status', $request->status);
            }
        }

        $appointments = $query
            ->orderBy('appointments.appointment_date', 'desc')
            ->orderBy('appointments.appointment_time')
            ->orderBy('appointments.serial')
            ->paginate(50)
            ->withQueryString();

        return view('appointments.index', compact('appointments'));
    }

    // ১. সকল অ্যাপয়েন্টমেন্ট দেখার জন্য (Approve Page)
    public function appointmentapprove()
    {
        // ডাটাবেস থেকে সব অ্যাপয়েন্টমেন্ট নিয়ে আসা (Join Query)
        $appointments = DB::table('appointments')
            ->join('patients', 'appointments.patient_id', '=', 'patients.id')
            ->select('appointments.*', 'patients.patientname')
            ->where(function ($query) {
                $query->whereNull('appointments.status')
                    ->orWhereIn('appointments.status', ['pending', 'approved']);
            })
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time')
            ->orderBy('serial')
            ->get();

        return view('appointments.approve', compact('appointments'));
    }

    // ২. নতুন যুক্ত করা মেথড: অ্যাপয়েন্টমেন্ট স্ট্যাটাস আপডেট/অ্যাপ্রুভ করা
    public function updateStatus(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|integer|exists:appointments,id',
            'status'         => 'nullable|in:approved,completed',
        ]);

        $id = $request->input('appointment_id');
        $status = $request->input('status', 'approved');

        // স্ট্যাটাস আপডেট
        $query = DB::table('appointments')->where('id', $id);

        if ($status === 'completed') {
            $query->where('status', 'approved');
        }

        $updated = $query->update([
            'status' => $status,
            'updated_at' => now()
        ]);

        if ($updated) {
            $message = $status === 'completed'
                ? 'Appointment marked as done successfully!'
                : 'Appointment approved successfully!';

            return back()->with('success', $message);
        }

        if ($status === 'completed') {
            return back()->with('error', 'Only approved appointments can be marked as done.');
        }

        return back()->with('error', 'Something went wrong. Please try again.');
    }

    // ৩. AJAX: নির্দিষ্ট তারিখের পরবর্তী সিরিয়াল নাম্বার বের করা
    public function nextSerial(Request $r)
    {
        $r->validate([
            'date' => ['required', 'date'],
        ]);

        $date = $r->get('date');
        $doctorId = $r->get('doctor_id');

        $query = DB::table('appointments')->whereDate('appointment_date', $date);
        if ($doctorId) $query->where('doctor_id', $doctorId);

        $count = (int) $query->count();
        $next  = $count + 1;

        return response()->json(['ok' => true, 'next' => $next]);
    }

    // ৪. অ্যাপয়েন্টমেন্ট তৈরি করা (Store Method)
    public function store(Request $r)
    {
        $data = $r->validate([
            'patient_id'       => ['required', 'integer', 'exists:patients,id'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'serial_no'        => ['required', 'integer', 'min:1', 'max:50'],
            'remarks'          => ['nullable', 'string', 'max:500'],
        ]);

        // একই তারিখে একই সিরিয়াল চেক করা
        $exists = DB::table('appointments')
            ->whereDate('appointment_date', $data['appointment_date'])
            ->where('serial', $data['serial_no'])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['serial_no' => 'This serial is already booked for the selected date.'])
                ->withInput();
        }

        // ডুপ্লিকেট অ্যাপয়েন্টমেন্ট চেক
        $already = DB::table('appointments')
            ->where('patient_id', $data['patient_id'])
            ->whereDate('appointment_date', $data['appointment_date'])
            ->exists();

        if ($already) {
            return back()->withErrors(['serial_no' => 'This patient already has an appointment on this date.'])
                         ->withInput();
        }

        DB::table('appointments')->insert([
            'patient_id'       => $data['patient_id'],
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $data['appointment_time'] ?? null,
            'serial'           => $data['serial_no'],
            'remarks'          => $data['remarks'] ?? null,
            'status'           => 'pending',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return back()->with('success', 'Appointment created successfully!');
    }

    // ৫. এভেইলেবল সিরিয়াল চেক করা (AJAX)
    public function availableSerials(Request $r)
    {
        $r->validate([
            'date'  => ['required', 'date'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);
        $date  = $r->get('date');
        $limit = (int)($r->get('limit', 50));

        $taken = DB::table('appointments')
            ->whereDate('appointment_date', $date)
            ->pluck('serial')
            ->map(fn($v) => (int)$v)
            ->toArray();

        $all        = range(1, $limit);
        $available  = array_values(array_diff($all, $taken));

        return response()->json(['ok' => true, 'available' => $available]);
    }

    // ৬. রোগীর অ্যাপয়েন্টমেন্ট চেক করা (AJAX)
    public function checkPatientDate(Request $r)
    {
        $r->validate([
            'patient_id' => 'required|integer|exists:patients,id',
            'date'       => 'required|date',
        ]);

        $exists = DB::table('appointments')
            ->where('patient_id', $r->patient_id)
            ->whereDate('appointment_date', $r->date)
            ->exists();

        return response()->json(['exists' => $exists]);
    }

    // ৭. ক্রিয়েট ভিউ দেখানো
    public function create()
    {
        $patients = DB::table('patients')->orderBy('patientname')->get();
        return view('appointments.create', compact('patients'));
    }
}
