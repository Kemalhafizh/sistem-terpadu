<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user', 'auditable');

        // Filter by User
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by Event
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Filter by Type
        if ($request->filled('type')) {
            $query->where('auditable_type', 'like', "%{$request->type}%");
        }

        // Filter by Date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->latest()->paginate(50)->withQueryString();

        $users = User::orderBy('name')->get();
        $events = ActivityLog::select('event')->distinct()->pluck('event');
        $types = ActivityLog::select('auditable_type')->distinct()->pluck('auditable_type')
            ->map(fn ($t) => ['full' => $t, 'short' => class_basename($t)]);

        return view('admin.logs.index', compact('logs', 'users', 'events', 'types'));
    }
}
