<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\CommonComplaint;
use App\Models\CommonDiagnosis;
use App\Models\CommonInvestigation;
use App\Models\CommonMedicine;
use App\Models\Bed;
use Illuminate\Support\Facades\DB;

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
    /* 3. Ward & Bed Management (New) */
    /* ////////////////////////*/

    // GET /settings/AddWardBed
    public function wardBedIndex()
    {
        $beds = Bed::orderBy('id', 'desc')->paginate(20);
        return view('settings.ward_bed', compact('beds'));
    }

    // POST /settings/wardbed
    public function wardBedStore(Request $request)
    {
        $request->validate([
            'bed_number'    => 'required|string|max:50|unique:beds,bed_number',
            'bed_type'      => 'required|string|max:100',
            'price_per_day' => 'nullable|numeric|min:0',
        ]);

        Bed::create([
            'bed_number'    => $request->bed_number,
            'bed_type'      => $request->bed_type,
            'price_per_day' => $request->price_per_day,
            'status'        => 'vacant',
        ]);

        return back()->with('success', 'Bed added successfully.');
    }

    // PUT /settings/wardbed/{id}
    public function wardBedUpdate(Request $request, $id)
    {
        $request->validate([
            'bed_number'    => 'required|string|max:50|unique:beds,bed_number,' . $id,
            'bed_type'      => 'required|string|max:100',
            'price_per_day' => 'nullable|numeric|min:0',
            'status'        => 'required|in:vacant,occupied,maintenance',
        ]);

        Bed::where('id', $id)->update([
            'bed_number'    => $request->bed_number,
            'bed_type'      => $request->bed_type,
            'price_per_day' => $request->price_per_day,
            'status'        => $request->status,
        ]);

        return back()->with('success', 'Bed updated successfully.');
    }

    // DELETE /settings/wardbed/{id}
    public function wardBedDestroy($id)
    {
        Bed::where('id', $id)->delete();
        return back()->with('success', 'Bed deleted successfully.');
    }

    /* ////////////////////////*/
    /* 4. Medicine Management */
    /* ////////////////////////*/

    // GET /settings/AddMedicine
    public function medicinesIndex()
    {
        $medicines = CommonMedicine::orderBy('id', 'desc')->paginate(20);
        return view('settings.medicines.index', compact('medicines'));
    }

    // POST /settings/medicines
    public function medicinesStore(Request $request)
    {
        $request->validate([
            'code'      => 'nullable|integer',
            'name'      => 'nullable|string',
            'GroupName' => 'required|string|max:100',
            'company' => 'required|string|max:50',
            'strength'  => 'required|string|max:15',
            'active'    => 'required|in:0,1',
        ]);

        $data = $request->only('code','name','GroupName','strength','company','active');
        $data['name_normalized'] = $this->normalizeName($request->name);

        CommonMedicine::create($data);

        return back()->with('success', 'Medicine added successfully.');
    }

    // PUT /settings/medicines/{medicine}
    public function medicinesUpdate(Request $request, CommonMedicine $medicine)
    {
        $request->validate([
            'code'      => 'nullable|integer',
            'name'      => 'nullable|string',
            'GroupName' => 'required|string|max:100',
            'company' => 'required|string|max:50',
            'strength'  => 'required|string|max:15',
            'active'    => 'required|in:0,1',
        ]);

        $data = $request->only('code','name','GroupName','strength','company','active');
        $data['name_normalized'] = $this->normalizeName($request->name);

        $medicine->update($data);

        return back()->with('success', 'Medicine updated successfully.');
    }

    // DELETE /settings/medicines/{medicine}
    public function medicinesDestroy(CommonMedicine $medicine)
    {
        $medicine->delete();
        return back()->with('success', 'Medicine deleted successfully.');
    }

    /* ////////////////////////*/
    /* 5. Helper Functions */
    /* ////////////////////////*/

    private function normalizeName($name)
    {
        $name = trim($name ?? '');
        $name = preg_replace('/\s+/', ' ', $name);
        return Str::lower($name);
    }

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