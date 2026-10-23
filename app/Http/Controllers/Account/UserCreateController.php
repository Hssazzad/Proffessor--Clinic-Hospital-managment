<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SubMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;

class UserCreateController extends Controller
{
    /**
     * Show the create-user form
     */
    public function newuser()
    {
        $role = RoleCheck(100101);
        if ($role !== true) {
            return $role;
        }
        return view('account.user.create');
    }

    /**
     * Handle storing the user
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'mobile'   => ['required', 'string', 'regex:/^01[0-9]{9}$/'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'mobile'      => $request->mobile,
            'password'    => Hash::make($request->password),
            'accounttype' => 1,
        ]);
        return back()->with('success', 'User created successfully!');
    }

    public function createSubmenu()
    {
        $parents = DB::table('parentmenu')
            ->select('parentcode as code', 'parentname as name')
            ->orderBy('parentname')
            ->get();
        return view('usermenu.submenu.create', compact('parents'));
    }

    public function nextSubmenuCode(int $parent)
    {
        $max = DB::table('submenu')
            ->where('menuparentcode', $parent)
            ->max('submenucode');
        $next = $max ? $max + 1 : intval($parent . '101');
        return response()->json([
            'ok'        => true,
            'next_code' => $next
        ]);
    }

    public function storeSubmenu(Request $request)
    {
        $data = $request->validate([
            'menuparentcode' => ['required', 'integer'],
            'submenucode'    => ['required', 'integer'],
            'submenuname'    => ['required', 'string', 'max:25'],
            'method'         => ['required', 'string', 'max:25'],
            'position'       => ['required', 'integer'],
        ]);
        SubMenu::create($data);
        return back()->with('status', 'Submenu created successfully!');
    }

    /**
     * Toggles the status of a submenu
     */
    public function toggleSub(Request $request)
    {
        $request->validate([
            'submenucode' => 'required|integer'
        ]);

        $submenu = DB::table('submenu')->where('submenucode', $request->submenucode)->first();

        if ($submenu) {
            // Safe check: If 'status' column doesn't exist in the object, default to 0
            $currentStatus = property_exists($submenu, 'status') ? $submenu->status : 0;
            
            // Toggle logic
            $newStatus = ($currentStatus == 1) ? 0 : 1;
            
            try {
                DB::table('submenu')
                    ->where('submenucode', $request->submenucode)
                    ->update(['status' => $newStatus]);

                return response()->json([
                    'success' => true,
                    'message' => 'Status updated to ' . ($newStatus ? 'Active' : 'Inactive'),
                    'new_status' => $newStatus
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                // If the update fails because the 'status' column is missing in DB
                return response()->json([
                    'success' => false, 
                    'message' => 'Database error: Column "status" may be missing in submenu table.'
                ], 500);
            }
        }

        return response()->json(['success' => false, 'message' => 'Submenu not found.'], 404);
    }

    public function createparentmenu()
    {
        $parents = DB::table('parentmenu')
            ->select('parentcode as code', 'parentname as name')
            ->orderBy('parentname')
            ->get();
        return view('usermenu.parentmenu.create', compact('parents'));
    }

    public function Parentstore(Request $request)
    {
        $request->validate([
            'parentcode'     => 'required|numeric|unique:parentmenu,parentcode',
            'parentname'     => 'required|string|max:255',
            'controllername' => 'required|string|max:255',
            'mainroutename'  => 'required|string|max:255',
        ]);
        DB::table('parentmenu')->insert([
            'parentcode'     => $request->parentcode,
            'parentname'     => $request->parentname,
            'controllername' => $request->controllername,
            'mainroute'      => $request->mainroutename,
        ]);
        return redirect()->back()->with('success', 'Parent menu added successfully!');
    }
    
     public function Userlist()
    {
        $users = DB::table('users')
            ->select(
                'id',
                'accounttype',
                'name',
                'mobile',
                'email',
                'email_verified_at',
                'created_at',
                'updated_at'
            )
            ->orderByDesc('id')
            ->get();

        return view('account.user.userlist', compact('users'));
    }
}