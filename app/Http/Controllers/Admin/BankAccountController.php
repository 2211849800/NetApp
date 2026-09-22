<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBankAccountRequest;
use App\Http\Requests\Admin\UpdateBankAccountRequest;
use App\Models\BankAccount;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BankAccountController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function index(): View
    {
        $bankAccounts = BankAccount::orderByDesc('created_at')->get();

        return view('admin.bank-accounts.index', compact('bankAccounts'));
    }

    public function create(): View
    {
        return view('admin.bank-accounts.create');
    }

    public function store(StoreBankAccountRequest $request): RedirectResponse
    {
        $account = BankAccount::create($request->validated());
        $this->auditLog->log('BANK_ACCOUNT_CREATED', 'BankAccount', $account->id, null, [
            'bank_name' => $account->bank_name,
            'account_name' => $account->account_name,
            'account_number' => $account->maskedAccountNumber(),
            'status' => $account->status->value,
        ]);

        return redirect()->route('admin.bank-accounts.index')->with('status', 'تم إنشاء الحساب البنكي.');
    }

    public function edit(BankAccount $bankAccount): View
    {
        return view('admin.bank-accounts.edit', compact('bankAccount'));
    }

    public function update(UpdateBankAccountRequest $request, BankAccount $bankAccount): RedirectResponse
    {
        $old = [
            'bank_name' => $bankAccount->bank_name,
            'account_name' => $bankAccount->account_name,
            'account_number' => $bankAccount->maskedAccountNumber(),
            'status' => $bankAccount->status->value,
        ];

        $bankAccount->update($request->validated());

        $this->auditLog->log('BANK_ACCOUNT_UPDATED', 'BankAccount', $bankAccount->id, $old, [
            'bank_name' => $bankAccount->bank_name,
            'account_name' => $bankAccount->account_name,
            'account_number' => $bankAccount->maskedAccountNumber(),
            'status' => $bankAccount->status->value,
        ]);

        return redirect()->route('admin.bank-accounts.index')->with('status', 'تم تحديث الحساب البنكي.');
    }

    public function toggleStatus(BankAccount $bankAccount): RedirectResponse
    {
        $old = $bankAccount->status?->value;
        $bankAccount->update([
            'status' => $bankAccount->status === RecordStatus::Active
                ? RecordStatus::Inactive
                : RecordStatus::Active,
        ]);

        $action = $bankAccount->status === RecordStatus::Active
            ? 'BANK_ACCOUNT_ACTIVATED'
            : 'BANK_ACCOUNT_DISABLED';

        $this->auditLog->log($action, 'BankAccount', $bankAccount->id, ['status' => $old], [
            'status' => $bankAccount->status->value,
        ]);

        return back()->with('status', 'تم تحديث حالة الحساب البنكي.');
    }
}
