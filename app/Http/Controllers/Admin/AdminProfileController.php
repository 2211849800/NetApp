<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangeAdminPasswordRequest;
use App\Http\Requests\Admin\UpdateAdminProfileRequest;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function index(): View
    {
        $user = auth()->user()->loadMissing('roles');

        return view('admin.profile.index', compact('user'));
    }

    public function update(UpdateAdminProfileRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $old = $user->only(['name', 'username', 'phone', 'email']);

        $user->update($request->validated());

        $this->auditLog->log('ADMIN_PROFILE_UPDATED', 'User', $user->id, $old, $user->only(['name', 'username', 'phone', 'email']));

        return redirect()->route('admin.profile.index')->with('status', 'تم تحديث بيانات الملف الشخصي بنجاح.');
    }

    public function changePassword(ChangeAdminPasswordRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        $this->auditLog->log('ADMIN_PASSWORD_CHANGED', 'User', $user->id);

        return redirect()->route('admin.profile.index')->with('status', 'تم تغيير كلمة المرور بنجاح.');
    }
}
