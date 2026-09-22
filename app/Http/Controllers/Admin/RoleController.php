<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateRolePermissionsRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function index(): View
    {
        $roles = Role::with('permissions')->whereIn('name', ['admin', 'supervisor', 'employee'])->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function edit(Role $role): View
    {
        abort_unless(in_array($role->name, ['admin', 'supervisor', 'employee']), 404);

        $permissions = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');

        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(UpdateRolePermissionsRequest $request, Role $role): RedirectResponse
    {
        abort_unless(in_array($role->name, ['admin', 'supervisor', 'employee']), 404);

        if ($role->name === 'admin') {
            return back()->with('error', 'صلاحيات دور المدير ثابتة ولا يمكن تعديلها.');
        }

        $old = $role->permissions->pluck('name')->all();
        $permissionIds = Permission::whereIn('name', $request->input('permissions', []))->pluck('id');
        $role->permissions()->sync($permissionIds);

        $this->auditLog->log('ROLE_PERMISSIONS_UPDATED', 'Role', $role->id, ['permissions' => $old], [
            'permissions' => $role->fresh('permissions')->permissions->pluck('name')->all(),
        ]);

        return redirect()->route('admin.roles.index')->with('status', 'تم تحديث صلاحيات الدور.');
    }
}
