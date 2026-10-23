<?php

namespace App\Http\Controllers;

use App\Models\BillingNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingNotificationController extends Controller
{
    public function index(Request $request): View
    {
        $query = BillingNotification::query()
            ->live()
            ->when(!$request->boolean('all'), fn ($q) => $q->where('is_read', false))
            ->orderByDesc('created_at');

        $notifications = $query->paginate(20);

        return view('billing-notifications.index', compact('notifications'));
    }

    public function markRead(BillingNotification $notification): RedirectResponse
    {
        $notification->update(['is_read' => true]);

        return back()->with('success', 'Notification marked as read.');
    }
}
