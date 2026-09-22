<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePackageRequest;
use App\Http\Requests\Admin\UpdatePackageRequest;
use App\Models\Package;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function index(): View
    {
        $packages = Package::orderByDesc('created_at')->get();

        return view('admin.packages.index', compact('packages'));
    }

    public function create(): View
    {
        return view('admin.packages.create');
    }

    public function store(StorePackageRequest $request): RedirectResponse
    {
        $package = Package::create($request->validated());
        $this->auditLog->logModelChange('PACKAGE_CREATED', $package);

        return redirect()->route('admin.packages.index')->with('status', 'تم إنشاء الباقة بنجاح.');
    }

    public function show(Package $package): View
    {
        return view('admin.packages.show', compact('package'));
    }

    public function edit(Package $package): View
    {
        return view('admin.packages.edit', compact('package'));
    }

    public function update(UpdatePackageRequest $request, Package $package): RedirectResponse
    {
        $old = $package->getAttributes();
        $package->update($request->validated());
        $this->auditLog->log('PACKAGE_UPDATED', 'Package', $package->id, $old, $package->getAttributes());

        return redirect()->route('admin.packages.index')->with('status', 'تم تحديث الباقة بنجاح.');
    }

    public function toggleStatus(Package $package): RedirectResponse
    {
        $old = $package->status?->value;
        $package->update([
            'status' => $package->status === RecordStatus::Active
                ? RecordStatus::Inactive
                : RecordStatus::Active,
        ]);

        $action = $package->status === RecordStatus::Active ? 'PACKAGE_ACTIVATED' : 'PACKAGE_DISABLED';
        $this->auditLog->log($action, 'Package', $package->id, ['status' => $old], ['status' => $package->status->value]);

        return back()->with('status', 'تم تحديث حالة الباقة.');
    }
}
