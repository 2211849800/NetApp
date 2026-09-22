<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLog,
    ) {}

    public function index(): View
    {
        $settings = [
            'subscriber_provider' => Setting::get('subscriber_provider', config('subscriber.provider', 'mock')),
            'emergency_borrowing_gb' => Setting::get('emergency_borrowing_gb', '70'),
            'emergency_borrowing_days' => Setting::get('emergency_borrowing_days', '3'),
            'currency_symbol' => Setting::get('currency_symbol', 'د.ل'),
            'support_phone' => Setting::get('support_phone', '0910000000'),
            'payment_mock_mode' => Setting::get('payment_mock_mode', 'true'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subscriber_provider' => ['required', 'in:mock,adv'],
            'emergency_borrowing_gb' => ['required', 'numeric', 'min:1'],
            'emergency_borrowing_days' => ['required', 'integer', 'min:1'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'support_phone' => ['required', 'string', 'max:20'],
            'payment_mock_mode' => ['required', 'in:true,false'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value, 'system', 'Dynamic system setting for '.$key);
        }

        $this->auditLog->log('SETTINGS_UPDATED', 'Setting', null, null, $validated);

        return back()->with('status', 'تم تحديث إعدادات النظام بنجاح.');
    }
}
