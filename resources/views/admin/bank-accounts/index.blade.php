@extends('layouts.app')

@section('title', 'الحسابات البنكية')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="إدارة الحسابات البنكية" subtitle="حسابات تحويل واستلام قيمة الاشتراكات" :action-url="route('admin.bank-accounts.create')" action-label="إضافة حساب بنكي" />
    <x-admin.flash />

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 font-bold">
                        <th class="py-4 px-6">المصرف</th>
                        <th class="py-4 px-6">اسم صاحب الحساب</th>
                        <th class="py-4 px-6">رقم الحساب</th>
                        <th class="py-4 px-6">الحالة</th>
                        <th class="py-4 px-6">تاريخ الإنشاء</th>
                        <th class="py-4 px-6">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse ($bankAccounts as $account)
                        <tr class="hover:bg-purple-50/20 transition">
                            <td class="py-4 px-6 font-bold text-gray-900">
                                <p class="font-bold text-gray-900">{{ $account->bank_name }}</p>
                                @if ($account->transfer_instructions)
                                    <p class="text-[11px] text-gray-400 font-normal truncate max-w-xs">{{ $account->transfer_instructions }}</p>
                                @endif
                            </td>
                            <td class="py-4 px-6 font-semibold">{{ $account->account_name }}</td>
                            <td class="py-4 px-6 font-mono text-purple-700 font-bold dir-ltr text-right">
                                {{ method_exists($account, 'maskedAccountNumber') ? $account->maskedAccountNumber() : $account->account_number }}
                            </td>
                            <td class="py-4 px-6">
                                @if ($account->status?->value === 'active')
                                    <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-semibold">نشط</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 font-semibold">معطل</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-gray-500">{{ $account->created_at?->format('Y-m-d') }}</td>
                            <td class="py-4 px-6">
                                <x-admin.resource-actions :item="$account" :edit-route="route('admin.bank-accounts.edit', $account)" :toggle-route="route('admin.bank-accounts.toggle-status', $account)" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 px-6 text-center text-gray-400">لا توجد حسابات بنكية مضافة بعد. ابدأ بإضافة حساب جديد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
