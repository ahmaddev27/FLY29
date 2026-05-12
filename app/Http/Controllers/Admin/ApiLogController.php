<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = ApiLog::latest();

        if ($q = $request->query('q')) {
            $query->where(function ($qb) use ($q) {
                $qb->where('endpoint', 'like', "%{$q}%")
                   ->orWhere('reference_id', 'like', "%{$q}%")
                   ->orWhere('ip_address', 'like', "%{$q}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($method = $request->query('method')) {
            $query->where('method', $method);
        }

        if ($from = $request->query('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->query('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->paginate(50)->withQueryString();

        // Summary tiles
        $stats = [
            'total'        => ApiLog::whereDate('created_at', today())->count(),
            'success'      => ApiLog::whereDate('created_at', today())->where('status', 'success')->count(),
            'duplicate'    => ApiLog::whereDate('created_at', today())->where('status', 'duplicate_ignored')->count(),
            'failed'       => ApiLog::whereDate('created_at', today())->whereIn('status', ['failed', 'unauthorized', 'rate_limited'])->count(),
        ];

        return view('admin.api-logs.index', compact('logs', 'stats'));
    }

    public function show(Request $request, ApiLog $log): View
    {
        return view('admin.api-logs.show', compact('log'));
    }
}
