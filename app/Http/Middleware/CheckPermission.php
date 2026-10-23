<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$submenucodes)
    {
        if (! auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        $userpin = auth()->id();

        foreach ($submenucodes as $code) {
            $hasPermission = DB::table('user_sub_menu')
                ->where('userpin', $userpin)
                ->where('submenucode', $code)
                ->exists();

            if ($hasPermission) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        abort(403, 'You do not have permission to access this page.');
    }
}
