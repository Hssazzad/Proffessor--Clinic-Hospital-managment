<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserMenuController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = DB::table('parentmenu');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('parentname', 'like', '%' . $search . '%')
                  ->orWhere('parentcode', 'like', '%' . $search . '%')
                  ->orWhere('controllername', 'like', '%' . $search . '%')
                  ->orWhere('mainroute', 'like', '%' . $search . '%');
            });
        }

        $menus = $query->paginate(10);

        if ($request->ajax()) {
            return response()->json($menus);
        }

        return view('usermenu.index', compact('menus'));
    }

    public function parentStore(Request $request)
    {
        $request->validate([
            'parentcode'     => 'required|integer',
            'parentname'     => 'required|string|max:25',
            'controllername' => 'required|string|max:25',
            'mainroutename'  => 'required|string|max:25',
        ]);

        DB::table('parentmenu')->insert([
            'parentcode'     => $request->parentcode,
            'parentname'     => $request->parentname,
            'controllername' => $request->controllername,
            'mainroute'      => $request->mainroutename,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Parent menu created successfully!']);
        }

        return redirect()->back()->with('success', 'Parent menu created successfully!');
    }

    public function edit(Request $request, $id)
    {
        $menu = DB::table('parentmenu')->where('id', $id)->first();

        if ($request->ajax()) {
            return response()->json($menu);
        }

        return view('usermenu.edit', compact('menu'));
    }

    public function submenulist(Request $request)
    {
        $search = $request->input('search');
        $query = DB::table('submenu');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('submenuname', 'like', '%' . $search . '%')
                  ->orWhere('method', 'like', '%' . $search . '%')
                  ->orWhere('submenucode', 'like', '%' . $search . '%')
                  ->orWhere('menuparentcode', 'like', '%' . $search . '%');
            });
        }

        $submenus = $query->paginate(10);

        if ($request->ajax()) {
            return response()->json($submenus);
        }

        return view('usermenu.submenulist', compact('submenus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'menuparentcode' => 'required|integer',
            'submenucode'    => 'required|integer',
            'submenuname'    => 'required|string|max:25',
            'method'         => 'required|string|max:25',
            'position'       => 'required|integer',
        ]);

        DB::table('submenu')->insert([
            'menuparentcode' => $request->menuparentcode,
            'submenucode'    => $request->submenucode,
            'submenuname'    => $request->submenuname,
            'method'         => $request->method,
            'position'       => $request->position,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Submenu created successfully!']);
        }

        return redirect()->back()->with('success', 'Submenu created successfully!');
    }

    public function update(Request $request, $id)
    {
        if ($request->has('parentname') || $request->has('controllername')) {
            $request->validate([
                'parentcode'     => 'required|integer',
                'parentname'     => 'required|string|max:25',
                'controllername' => 'required|string|max:25',
                'mainroutename'  => 'required|string|max:25',
            ]);

            DB::table('parentmenu')->where('id', $id)->update([
                'parentcode'     => $request->parentcode,
                'parentname'     => $request->parentname,
                'controllername' => $request->controllername,
                'mainroute'      => $request->mainroutename,
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Parent menu updated successfully!']);
            }

            return redirect()->back()->with('success', 'Parent menu updated successfully!');
        }

        $request->validate([
            'menuparentcode' => 'required|integer',
            'submenucode'    => 'required|integer',
            'submenuname'    => 'required|string|max:25',
            'method'         => 'required|string|max:25',
            'position'       => 'required|integer',
        ]);

        DB::table('submenu')->where('id', $id)->update([
            'menuparentcode' => $request->menuparentcode,
            'submenucode'    => $request->submenucode,
            'submenuname'    => $request->submenuname,
            'method'         => $request->method,
            'position'       => $request->position,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Submenu updated successfully!']);
        }

        return redirect()->back()->with('success', 'Submenu updated successfully!');
    }

    public function destroy(Request $request, $id)
    {
        if ($request->is('usermenu/mainmenu/*') || $request->segment(2) === 'mainmenu') {
            DB::table('parentmenu')->where('id', $id)->delete();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Parent menu deleted successfully!']);
            }

            return redirect()->back()->with('success', 'Parent menu deleted successfully!');
        }

        DB::table('submenu')->where('id', $id)->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Submenu deleted successfully!']);
        }

        return redirect()->back()->with('success', 'Submenu deleted successfully!');
    }
}