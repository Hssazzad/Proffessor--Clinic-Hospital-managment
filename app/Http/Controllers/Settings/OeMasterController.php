<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\OeMaster;
use Illuminate\Http\Request;

class OeMasterController extends Controller
{
    public const FIELD_CODES = [
        1 => 'Pulse',
        2 => 'BP',
        3 => 'Temperature',
        4 => 'Weight',
        5 => 'Height',
        6 => 'SpO₂',
        7 => 'Respiratory Rate (RR)',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $oeMasters = OeMaster::orderBy('sort_order')->orderBy('name')->get();

        return view('settings.oe.index', [
            'oeMasters'  => $oeMasters,
            'fieldCodes' => self::FIELD_CODES,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('settings.oe.create', ['fieldCodes' => self::FIELD_CODES]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate($this->validationRules());

        OeMaster::create([
            'name'       => trim($request->name),
            'short_name' => $request->short_name,
            'unit'       => $request->unit,
            'input_type' => $request->input_type,
            'sort_order' => $request->sort_order,
            'field_code' => $request->field_code,
            'active'     => $request->boolean('active') ? 1 : 0,
        ]);

        return redirect()->route('settings.oe.index')->with('success', 'Created Successfully');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OeMaster $oe)
    {
        return view('settings.oe.edit', [
            'oe'         => $oe,
            'fieldCodes' => self::FIELD_CODES,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OeMaster $oe)
    {
        $request->validate($this->validationRules($oe->id));

        $oe->update([
            'name'       => trim($request->name),
            'short_name' => $request->short_name,
            'unit'       => $request->unit,
            'input_type' => $request->input_type,
            'sort_order' => $request->sort_order,
            'field_code' => $request->field_code,
            'active'     => $request->boolean('active') ? 1 : 0,
        ]);

        return redirect()->route('settings.oe.index')->with('success', 'Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OeMaster $oe)
    {
        $oe->delete();

        return redirect()->route('settings.oe.index')->with('success', 'Deleted Successfully');
    }

    /**
     * Shared validation rules.
     */
    private function validationRules($ignoreId = null): array
    {
        return [
            'name'       => ['required', 'string', 'max:100', 'unique:oe_master,name' . ($ignoreId ? ',' . $ignoreId : '')],
            'short_name' => ['nullable', 'string', 'max:30'],
            'unit'       => ['nullable', 'string', 'max:20'],
            'input_type' => ['required', 'in:text,number,decimal,textarea'],
            'sort_order' => ['required', 'numeric'],
            'field_code' => ['required', 'integer', 'in:' . implode(',', array_keys(self::FIELD_CODES))],
            'active'     => ['nullable', 'boolean'],
        ];
    }
}
