<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        // Audit log is sensitive — only super_admin may view it.
        abort_unless($request->user()->isSuperAdmin(), 403, 'سجل التدقيق متاح للسوبر أدمن فقط.');

        $query = AuditLog::with('user')->latest();

        if ($q = $request->query('q')) {
            $query->where(function ($qb) use ($q) {
                $qb->where('action', 'like', "%{$q}%")
                   ->orWhere('entity_id', 'like', "%{$q}%")
                   ->orWhere('entity_type', 'like', "%{$q}%")
                   ->orWhereHas('user', fn ($u) => $u
                        ->where('full_name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%"));
            });
        }

        if ($action = $request->query('action')) {
            $query->where('action', $action);
        }

        if ($userId = $request->query('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($from = $request->query('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->query('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->paginate(50)->withQueryString();

        // For the filter dropdown
        $actions = AuditLog::distinct()->orderBy('action')->pluck('action')->mapWithKeys(fn ($a) => [$a => $a])->toArray();

        return view('admin.audit.index', compact('logs', 'actions'));
    }
}
