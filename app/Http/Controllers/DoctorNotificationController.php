<?php

namespace App\Http\Controllers;

use App\Models\DoctorNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorNotificationController extends Controller
{
    public function index(Request $request): View
    {
        $query = DoctorNotification::query()
            ->with(['followup.patient', 'followup.report'])
            ->where('is_read', false);

        $notifications = $query->orderByDesc('created_at')->paginate(20);

        return view('doctor-notifications.index', compact('notifications'));
    }

    public function markRead(DoctorNotification $notification): RedirectResponse
    {
        $notification->update(['is_read' => true]);

        return back()->with('success', 'Notification marked as read.');
    }
}
