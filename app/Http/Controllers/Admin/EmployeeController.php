<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResetEmployeePasswordRequest;
use App\Http\Requests\Admin\StoreEmployeeRequest;
use App\Http\Requests\Admin\UpdateEmployeeRequest;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Services\Employee\EmployeeService;
use App\Services\Employee\EmployeeWalletService;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function __construct(
        private readonly EmployeeService $employeeService,
        private readonly EmployeeWalletService $wallet,
        private readonly AuditLogService $auditLog,
    ) {}

    public function index(): View
    {
        $staffRoles = Role::whereIn('name', User::STAFF_ROLES)->pluck('id');

        $employees = User::query()
            ->whereHas('roles', fn ($q) => $q->whereIn('roles.id', $staffRoles))
            ->with('roles')
            ->orderByDesc('created_at')
            ->get();

        $walletBalances = $employees->mapWithKeys(fn (User $employee) => [
            $employee->id => $this->wallet->balance($employee),
        ]);

        return view('admin.employees.index', compact('employees', 'walletBalances'));
    }

    public function creditWallet(Request $request, User $employee): RedirectResponse
    {
        abort_unless($employee->hasRole('employee'), 404);
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:10000000'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $transaction = $this->wallet->credit(
            $employee,
            $request->user(),
            (float) $validated['amount'],
            $validated['note'] ?? null,
        );
        $this->auditLog->log('EMPLOYEE_WALLET_FUNDED', 'EmployeeWalletTransaction', $transaction->id, null, [
            'employee_id' => $employee->id,
            'amount' => $transaction->amount,
            'reference' => $transaction->reference,
        ]);

        return back()->with('status', 'تم تحويل المبلغ إلى رصيد الموظف '.$employee->name.'.');
    }

    public function create(): View
    {
        $canCreateAdmin = auth()->user()->hasPermission('manage_admins');

        return view('admin.employees.create', compact('canCreateAdmin'));
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $this->employeeService->createStaffMember($request->validated());

        return redirect()->route('admin.employees.index')
            ->with('status', 'تم إنشاء حساب الموظف بنجاح.');
    }

    public function show(User $employee): View
    {
        $this->ensureStaffMember($employee);
        $employee->load('roles');
        $activity = AuditLog::where('entity_type', 'User')
            ->where('entity_id', $employee->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.employees.show', compact('employee', 'activity'));
    }

    public function edit(User $employee): View
    {
        $this->ensureStaffMember($employee);
        $canAssignAdmin = auth()->user()->hasPermission('manage_admins');

        return view('admin.employees.edit', compact('employee', 'canAssignAdmin'));
    }

    public function update(UpdateEmployeeRequest $request, User $employee): RedirectResponse
    {
        $this->ensureStaffMember($employee);
        $this->employeeService->updateStaffMember($employee, $request->validated());

        return redirect()->route('admin.employees.index')->with('status', 'تم تحديث بيانات الموظف.');
    }

    public function resetPasswordForm(User $employee): View
    {
        $this->ensureStaffMember($employee);

        return view('admin.employees.reset-password', compact('employee'));
    }

    public function resetPassword(ResetEmployeePasswordRequest $request, User $employee): RedirectResponse
    {
        $this->ensureStaffMember($employee);
        $this->employeeService->resetPassword($employee, $request->validated('password'));

        return redirect()->route('admin.employees.show', $employee)
            ->with('status', 'تم إعادة تعيين كلمة المرور بنجاح.');
    }

    public function activate(User $employee): RedirectResponse
    {
        $this->ensureStaffMember($employee);
        $this->employeeService->updateStatus($employee, UserStatus::Active);

        return back()->with('status', 'تم تفعيل حساب الموظف.');
    }

    public function deactivate(Request $request, User $employee): RedirectResponse
    {
        $this->ensureStaffMember($employee);
        $request->validate(['confirm' => ['required', 'in:yes']]);
        $this->employeeService->updateStatus($employee, UserStatus::Inactive);

        return back()->with('status', 'تم تعطيل حساب الموظف.');
    }

    public function suspend(Request $request, User $employee): RedirectResponse
    {
        $this->ensureStaffMember($employee);
        $request->validate(['confirm' => ['required', 'in:yes']]);
        $this->employeeService->updateStatus($employee, UserStatus::Suspended);

        return back()->with('status', 'تم إيقاف حساب الموظف.');
    }

    private function ensureStaffMember(User $employee): void
    {
        abort_unless($employee->isStaff(), 404);
    }
}
