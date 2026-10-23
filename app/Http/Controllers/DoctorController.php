<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{

    public function index()
    {
        $doctors = Doctor::orderByDesc('id')->paginate(20);

        $specialities = DB::table('configspeciality')->orderBy('name')->get();

        return view('settings.doctors.index', compact('doctors', 'specialities'));
    }

    public function create()
    {
        return redirect()->route('settings.doctors.index');
    }

    public function ajaxList(Request $r)
    {
        $query = DB::table('doctors as d')
            ->leftJoin('configspeciality as s', 's.id', '=', 'd.speciality')
            ->select(
                'd.id',
                'd.reg_no',
                'd.doctor_name',
                'd.speciality',
                's.name as speciality_name',
                'd.contact',
                'd.Posting',
                'd.RateCode',
                'd.active'
            );

        if ($r->filled('search')) {
            $q = $r->search;
            $query->where(function ($w) use ($q) {
                $w->where('d.doctor_name', 'like', '%' . $q . '%')
                  ->orWhere('d.reg_no', 'like', '%' . $q . '%')
                  ->orWhere('d.contact', 'like', '%' . $q . '%')
                  ->orWhere('s.name', 'like', '%' . $q . '%');
            });
        }

        $items = $query->orderByDesc('d.id')->get();

        return response()->json(['ok' => true, 'data' => $items]);
    }

    public function store(Request $r)
    {
        $r->validate([
            'reg_no'      => 'nullable|string|max:12',
            'doctor_name' => 'required|string|max:50',
            'speciality'  => 'nullable|string|max:25',
            'contact'     => 'nullable|string|max:12',
            'Posting'     => 'nullable|string|max:25',
            'RateCode'    => 'nullable|string|max:12',
        ]);

        $item = Doctor::create([
            'reg_no'      => $r->reg_no,
            'doctor_name' => $r->doctor_name,
            'speciality'  => $r->speciality,
            'contact'     => $r->contact,
            'Posting'     => $r->Posting,
            'RateCode'    => $r->RateCode,
            'active'      => $r->has('active') ? $r->active : 1,
        ]);

        if ($r->ajax() || $r->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Doctor added successfully!', 'data' => $item]);
        }

        return redirect()
            ->route('settings.doctors.index')
            ->with('success', 'Doctor added successfully!');
    }

    public function edit($id)
    {
        $doctor = Doctor::findOrFail($id);

        return view('settings.doctors.edit', compact('doctor'));
    }

    public function update(Request $r, $id)
    {
        $r->validate([
            'reg_no'      => 'nullable|string|max:12',
            'doctor_name' => 'required|string|max:50',
            'speciality'  => 'nullable|string|max:25',
            'contact'     => 'nullable|string|max:12',
            'Posting'     => 'nullable|string|max:25',
            'RateCode'    => 'nullable|string|max:12',
        ]);

        $doctor = Doctor::findOrFail($id);

        $doctor->update([
            'reg_no'      => $r->reg_no,
            'doctor_name' => $r->doctor_name,
            'speciality'  => $r->speciality,
            'contact'     => $r->contact,
            'Posting'     => $r->Posting,
            'RateCode'    => $r->RateCode,
            'active'      => $r->has('active') ? $r->active : 1,
        ]);

        if ($r->ajax() || $r->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Doctor updated successfully!', 'data' => $doctor]);
        }

        return redirect()
            ->route('settings.doctors.index')
            ->with('success', 'Doctor updated successfully!');
    }

    public function destroy(Request $r, $id)
    {
        $doctor = Doctor::findOrFail($id);
        $doctor->delete();

        if ($r->ajax() || $r->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Doctor deleted successfully!']);
        }

        return redirect()
            ->route('settings.doctors.index')
            ->with('success', 'Doctor deleted successfully!');
    }
    
    // ✅ নতুন মেথড: active/inactive টগল করার জন্য
    public function toggleActive($id)
    {
        $doctor = Doctor::findOrFail($id);
        $doctor->active = !$doctor->active;
        $doctor->save();
        
        return redirect()
            ->route('settings.doctors.index')
            ->with('success', 'Doctor status updated successfully!');
    }
}