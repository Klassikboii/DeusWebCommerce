<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        // Fitur Pencarian & Filter
        $query = UserActivity::with('user')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('action_filter')) {
            $query->where('action', $request->action_filter);
        }

        // Ambil daftar aksi unik untuk opsi filter dropdown
        $uniqueActions = UserActivity::select('action')->distinct()->pluck('action');

        $logs = $query->paginate(50)->withQueryString();

        return view('admin.audit_logs.index', compact('logs', 'uniqueActions'));
    }
}