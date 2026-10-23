<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class TreatmentTypeController extends Controller
{
    public function index()
    {
        $activeMenu = 'settings/treatment-types';
        
        // Fetch all treatment types
        $types = DB::table('treatment_types')
            ->orderBy('id', 'desc')
            ->get();

        return view('settings.treatment_types.index', compact('types', 'activeMenu'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:treatment_types,name',
        ]);

        try {
            DB::table('treatment_types')->insert([
                'name' => $request->name,
                'skip_pre_surgery' => $request->input('skip_pre_surgery', 0),
                'skip_post_surgery' => $request->input('skip_post_surgery', 0),
                'skip_round_patient' => $request->input('skip_round_patient', 0),
                'skip_fresh_prescription' => $request->input('skip_fresh_prescription', 0),
                'status' => 1,
                // 'created_at' and 'updated_at' removed to avoid SQL errors
            ]);

            return redirect()->back()->with('success', 'Treatment Type created successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:treatment_types,name,' . $id,
            'status' => 'required|boolean',
        ]);

        try {
            DB::table('treatment_types')
                ->where('id', $id)
                ->update([
                    'name' => $request->name,
                    'skip_pre_surgery' => $request->input('skip_pre_surgery', 0),
                    'skip_post_surgery' => $request->input('skip_post_surgery', 0),
                    'skip_round_patient' => $request->input('skip_round_patient', 0),
                    'skip_fresh_prescription' => $request->input('skip_fresh_prescription', 0),
                    'status' => $request->status,
                    // 'updated_at' removed to avoid SQL errors
                ]);

            return redirect()->back()->with('success', 'Treatment Type updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::table('treatment_types')->where('id', $id)->delete();
            return redirect()->back()->with('success', 'Treatment Type deleted successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
