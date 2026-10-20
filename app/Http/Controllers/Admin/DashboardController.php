<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardStatsService;
use Illuminate\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardStatsService $statsService) {}

    public function index(Request $request): View
    {
        $days = in_array((int) $request->query('period', 7), [7, 30], true) ? (int) $request->query('period', 7) : 7;

        return view('admin.dashboard.index', [
            'stats' => $this->statsService->getStats($days),
        ]);
    }
}
