<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreIptvPackageRequest;
use App\Http\Requests\Admin\UpdateIptvPackageRequest;
use App\Models\IptvPackage;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class IptvPackageController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function index(): View
    {
        $iptvPackages = IptvPackage::orderByDesc('created_at')->get();

        return view('admin.iptv-packages.index', compact('iptvPackages'));
    }

    public function create(): View
    {
        return view('admin.iptv-packages.create');
    }

    public function store(StoreIptvPackageRequest $request): RedirectResponse
    {
        $iptv = IptvPackage::create($request->validated());
        $this->auditLog->logModelChange('IPTV_PACKAGE_CREATED', $iptv);

        return redirect()->route('admin.iptv-packages.index')->with('status', 'تم إنشاء باقة IPTV بنجاح.');
    }

    public function edit(IptvPackage $iptvPackage): View
    {
        return view('admin.iptv-packages.edit', compact('iptvPackage'));
    }

    public function update(UpdateIptvPackageRequest $request, IptvPackage $iptvPackage): RedirectResponse
    {
        $old = $iptvPackage->getAttributes();
        $iptvPackage->update($request->validated());
        $this->auditLog->log('IPTV_PACKAGE_UPDATED', 'IptvPackage', $iptvPackage->id, $old, $iptvPackage->getAttributes());

        return redirect()->route('admin.iptv-packages.index')->with('status', 'تم تحديث باقة IPTV.');
    }

    public function toggleStatus(IptvPackage $iptvPackage): RedirectResponse
    {
        $old = $iptvPackage->status?->value;
        $iptvPackage->update([
            'status' => $iptvPackage->status === RecordStatus::Active
                ? RecordStatus::Inactive
                : RecordStatus::Active,
        ]);

        $this->auditLog->log('IPTV_PACKAGE_STATUS_CHANGED', 'IptvPackage', $iptvPackage->id, ['status' => $old], [
            'status' => $iptvPackage->status->value,
        ]);

        return back()->with('status', 'تم تحديث حالة باقة IPTV.');
    }
}
