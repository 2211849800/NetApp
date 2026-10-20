<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Complaint;
use App\Models\RechargeRequest;
use App\Services\Employee\EmployeeDashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly EmployeeDashboardService $dashboardService,
    ) {}

    public function index(): View
    {
        $employee = request()->user();
        $stats = $this->dashboardService->getStats($employee->id);

        $latestRecharges = RechargeRequest::query()
            ->whereIn('status', ['pending', 'under_review'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $latestComplaints = Complaint::query()
            ->whereIn('status', ['open', 'new', 'under_review', 'in_progress'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $todayOperations = AuditLog::query()
            ->with('user')
            ->where('user_id', $employee->id)
            ->whereDate('created_at', now()->toDateString())
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('employee.dashboard', [
            'roleLabel' => 'موظف',
            'stats' => $stats,
            'latestRecharges' => $latestRecharges,
            'latestComplaints' => $latestComplaints,
            'todayOperations' => $todayOperations,
        ]);
    }
}
