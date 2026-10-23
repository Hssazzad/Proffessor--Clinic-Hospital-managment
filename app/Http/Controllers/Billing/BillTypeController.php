<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\BillType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BillTypeController extends Controller
{
   
    public function index()
    {
        $billTypes = BillType::orderBy('sort_order', 'asc')->get();
        return view('billing.bill_types.index', compact('billTypes'));
    }

   
    public function create()
    {
        return view('billing.bill_types.create');
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'slug'       => 'required|string|max:50|unique:bill_types,slug',
            'icon'       => 'nullable|string|max:50',
            'color'      => 'nullable|string|max:20',
            'main_code'  => 'nullable|integer',
            'sort_order' => 'nullable|integer'
        ]);

        BillType::create([
            'name'              => $request->name,
            'slug'              => Str::slug($request->slug, '_'),
            'icon'              => $request->icon ?? 'fa-file-invoice',
            'color'             => $request->color ?? '#1a3a5c',
            'main_code'         => $request->main_code,
            'requires_doctor'   => $request->has('requires_doctor') ? 1 : 0,
            'requires_category' => $request->has('requires_category') ? 1 : 0,
            'free_text_items'   => $request->has('free_text_items') ? 1 : 0,
            'is_active'         => $request->has('is_active') ? 1 : 0,
            'sort_order'        => $request->sort_order ?? 0,
        ]);

        
        return response()->json([
            'success' => true, 
            'message' => 'Bill Type added successfully.'
        ]);
    }

   
    public function update(Request $request, $id)
    {
        $billType = BillType::findOrFail($id);

        $request->validate([
            'name'       => 'required|string|max:100',
            'slug'       => 'required|string|max:50|unique:bill_types,slug,' . $billType->id,
            'icon'       => 'nullable|string|max:50',
            'color'      => 'nullable|string|max:20',
            'main_code'  => 'nullable|integer',
            'sort_order' => 'nullable|integer'
        ]);

        $billType->update([
            'name'              => $request->name,
            'slug'              => Str::slug($request->slug, '_'),
            'icon'              => $request->icon ?? 'fa-file-invoice',
            'color'             => $request->color ?? '#1a3a5c',
            'main_code'         => $request->main_code,
            'requires_doctor'   => $request->has('requires_doctor') ? 1 : 0,
            'requires_category' => $request->has('requires_category') ? 1 : 0,
            'free_text_items'   => $request->has('free_text_items') ? 1 : 0,
            'is_active'         => $request->has('is_active') ? 1 : 0,
            'sort_order'        => $request->sort_order ?? 0,
        ]);

        
        return response()->json([
            'success' => true, 
            'message' => 'Bill Type updated successfully.'
        ]);
    }

    
    public function destroy($id)
    {
        $billType = BillType::findOrFail($id);
        $billType->delete();

        
        return response()->json([
            'success' => true, 
            'message' => 'Bill Type deleted successfully.'
        ]);
    }
}