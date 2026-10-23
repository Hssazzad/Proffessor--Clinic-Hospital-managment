<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicineType;
use App\Models\MedicineSubtype;
use Illuminate\Http\Request;

class MedicineTypeController extends Controller
{
    public function index()
    {
        $types = MedicineType::withCount('subTypes')->orderBy('type_name')->paginate(20);
        $allTypes = MedicineType::where('active', 1)->orderBy('type_name')->get();
        return view('settings.types.index', compact('types', 'allTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type_name' => 'required|string|max:150|unique:medicine_types,type_name',
        ]);

        MedicineType::create([
            'type_name' => $request->type_name,
            'active'    => 1,
        ]);

        return redirect()->route('settings.types.index')->with('success', 'Medicine Type created successfully.');
    }

    public function update(Request $request, MedicineType $medicineType)
    {
        $request->validate([
            'type_name' => 'required|string|max:150|unique:medicine_types,type_name,' . $medicineType->id,
            'active'    => 'required|in:0,1',
        ]);

        $medicineType->update($request->only('type_name', 'active'));

        return redirect()->route('settings.types.index')->with('success', 'Medicine Type updated successfully.');
    }

    public function destroy(MedicineType $medicineType)
    {
        $medicineType->subTypes()->delete();
        $medicineType->delete();

        return redirect()->route('settings.types.index')->with('success', 'Medicine Type and its Sub Types deleted successfully.');
    }

    public function subTypesAjax(MedicineType $medicineType)
    {
        return $medicineType->activeSubTypes()->orderBy('subtype_name')->get(['id', 'subtype_name']);
    }

    public function subTypeStore(Request $request)
    {
        $request->validate([
            'medicine_type_id' => 'required|exists:medicine_types,id',
            'subtype_name'     => 'required|string|max:150',
        ]);

        $exists = MedicineSubtype::where('medicine_type_id', $request->medicine_type_id)
            ->where('subtype_name', $request->subtype_name)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This Sub Type already exists under the selected Type.',
            ], 422);
        }

        $subType = MedicineSubtype::create([
            'medicine_type_id' => $request->medicine_type_id,
            'subtype_name'     => $request->subtype_name,
            'active'           => 1,
        ]);

        return response()->json([
            'success'      => true,
            'id'           => $subType->id,
            'subtype_name' => $subType->subtype_name,
        ]);
    }

    public function subTypeUpdate(Request $request, MedicineSubtype $subType)
    {
        $request->validate([
            'subtype_name' => 'required|string|max:150',
        ]);

        $subType->update($request->only('subtype_name', 'active'));

        return response()->json([
            'success'      => true,
            'subtype_name' => $subType->subtype_name,
        ]);
    }

    public function subTypeDestroy(MedicineSubtype $subType)
    {
        $subType->delete();

        return response()->json(['success' => true]);
    }
}
