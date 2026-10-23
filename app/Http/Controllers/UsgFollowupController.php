<?php

namespace App\Http\Controllers;

use App\Models\UsgFollowup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class UsgFollowupController extends Controller
{
    public function index(Request $request): View
    {
        $today = now()->toDateString();

        $base = UsgFollowup::query()
            ->with(['patient', 'doctor', 'report'])
            ->orderBy('followup_date', 'asc');

        $todayFollowups = (clone $base)->where('status', 'pending')
            ->whereDate('followup_date', $today)->get();

        $overdueFollowups = (clone $base)->where('status', 'pending')
            ->whereDate('followup_date', '<', $today)->get();

        $upcomingFollowups = (clone $base)->where('status', 'pending')
            ->whereDate('followup_date', '>', $today)->get();

        $contactedFollowups = (clone $base)->where('status', 'contacted')->get();

        $completedFollowups = (clone $base)->where('status', 'completed')->get();

        return view('usg.followups', compact(
            'todayFollowups',
            'overdueFollowups',
            'upcomingFollowups',
            'contactedFollowups',
            'completedFollowups'
        ));
    }

    public function markContacted(Request $request, UsgFollowup $usgFollowup): RedirectResponse
    {
        $usgFollowup->update([
            'status' => 'contacted',
            'contacted_at' => now(),
            'contacted_by' => Auth::id(),
            'notes' => $request->input('notes', $usgFollowup->notes),
        ]);

        return back()->with('success', 'Marked as contacted.');
    }

    public function complete(UsgFollowup $usgFollowup): RedirectResponse
    {
        $usgFollowup->update([
            'status' => 'completed',
            'contacted_at' => $usgFollowup->contacted_at ?? now(),
            'contacted_by' => $usgFollowup->contacted_by ?? Auth::id(),
        ]);

        return back()->with('success', 'Follow-up completed.');
    }

    public function cancel(UsgFollowup $usgFollowup): RedirectResponse
    {
        $usgFollowup->update(['status' => 'cancelled']);
        $usgFollowup->notifications()->delete();

        return back()->with('success', 'Follow-up cancelled.');
    }
}
