<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Services\ManagerDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private ManagerDashboardService $dashboard) {}

    public function index(Request $request): View
    {
        return view('manager.dashboard', $this->dashboard->aggregate($request->user()));
    }
}
