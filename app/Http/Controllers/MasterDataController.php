<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\CommonComplaint;
use App\Models\CommonDiagnosis;
use App\Models\CommonInvestigation;
use App\Models\Bed;

class MasterDataController extends Controller
{
    // ===== 1. JSON Lists (For AJAX/Select2) =====
    public function complaintsIndex()
    {
        return CommonComplaint::where('active', 1)->orderBy('name')->get(['id','name']);
    }

    public function diagnosesIndex()
    {
        return CommonDiagnosis::where('active', 1)->orderBy('name')->get(['id','name']);
    }

    public function investigationsIndex()
    {
        return CommonInvestigation::where('active', 1)->orderBy('name')->get(['id','name']);
    }

    // ===== 2. Store JSON Data with Normalization =====
    public function complaintsStore(Request $request)
    {
        $name = $this->validatedName($request);
        $row = CommonComplaint::firstOrCreate(
            ['name_normalized' => $this->normalize($name)],
            ['name' => $name, 'active' => 1]
        );
        return response()->json(['id'=>$row->id,'name'=>$row->name]);
    }

    public function diagnosesStore(Request $request)
    {
        $name = $this->validatedName($request);
        $row = CommonDiagnosis::firstOrCreate(
            ['name_normalized' => $this->normalize($name)],
            ['name' => $name, 'active' => 1]
        );
        return response()->json(['id'=>$row->id,'name'=>$row->name]);
    }

    public function investigationsStore(Request $request)
    {
        $name = $this->validatedName($request);
        $row = CommonInvestigation::firstOrCreate(
            ['name_normalized' => $this->normalize($name)],
            ['name' => $name, 'active' => 1]
        );
        return response()->json(['id'=>$row->id,'name'=>$row->name]);
    }

    /* ////////////////////////*/
    /* 3. Ward & Bed Management */
    /* ////////////////////////*/

    public function wardBedIndex()
    {
        $beds = Bed::orderBy('id', 'desc')->paginate(20);
        return view('settings.ward_bed', compact('beds'));
    }

    public function wardBedStore(Request $request)
    {
        $request->validate([
            'bed_name'      => 'required|string|max:100',
            'bed_number'    => 'required|string|max:50|unique:beds,bed_number',
            'bed_type'      => 'required|string|max:100',
            'price_per_day' => 'nullable|numeric|min:0',
        ]);

        Bed::create([
            'bed_name'      => $request->bed_name,
            'bed_number'    => $request->bed_number,
            'bed_type'      => $request->bed_type,
            'price_per_day' => $request->price_per_day,
            'status'        => 'vacant',
        ]);

        return back()->with('success', 'Bed added successfully.');
    }

    public function wardBedUpdate(Request $request, $id)
    {
        $request->validate([
            'bed_name'      => 'required|string|max:100',
            'bed_number'    => 'required|string|max:50|unique:beds,bed_number,' . $id,
            'bed_type'      => 'required|string|max:100',
            'price_per_day' => 'nullable|numeric|min:0',
            'status'        => 'required|in:vacant,occupied,maintenance',
        ]);

        Bed::where('id', $id)->update([
            'bed_name'      => $request->bed_name,
            'bed_number'    => $request->bed_number,
            'bed_type'      => $request->bed_type,
            'price_per_day' => $request->price_per_day,
            'status'        => $request->status,
        ]);

        return back()->with('success', 'Bed updated successfully.');
    }

    public function wardBedDestroy($id)
    {
        Bed::where('id', $id)->delete();
        return back()->with('success', 'Bed deleted successfully.');
    }

    /* ////////////////////////*/
    /* Helper Functions         */
    /* ////////////////////////*/

    private function validatedName(Request $request): string
    {
        $data = $request->validate(['name' => 'required|string|max:150']);
        return trim($data['name']);
    }

    private function normalize(string $s): string
    {
        $s = preg_replace('/\s+/', ' ', trim($s));
        return Str::lower($s);
    }
}
