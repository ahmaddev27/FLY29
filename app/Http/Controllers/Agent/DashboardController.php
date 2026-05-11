<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Services\AgentDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private AgentDashboardService $dashboard) {}

    public function index(Request $request): View
    {
        $agent = $request->user()->agent
            ?? abort(404, 'لا يوجد ملف وكيل مرتبط بهذا الحساب.');

        $data = $this->dashboard->aggregate($agent);

        return view('agent.dashboard', $data);
    }
}
