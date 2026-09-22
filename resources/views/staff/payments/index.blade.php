@extends('layouts.app')

@section('title', 'سجل المدفوعات')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="سجل المدفوعات والمعاملات" subtitle="المعاملات المالية المسجّلة من بوابات الدفع وعمليات الموظف" />
    <x-admin.flash />



    <form method="GET" class="bg-white p-5 rounded-3xl border border-gray-100 grid grid-cols-1 md:grid-cols-5 gap-3">
        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="مرجع / عقد / اسم"
               class="md:col-span-2 px-4 py-2.5 rounded-xl border text-xs">
        <select name="gateway" class="px-4 py-2.5 rounded-xl border text-xs">
            <option value="">كل البوابات</option>
            @foreach ($gateways as $value => $label)
                <option value="{{ $value }}" @selected(($filters['gateway'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status" class="px-4 py-2.5 rounded-xl border text-xs">
            <option value="">كل الحالات</option>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <div class="md:col-span-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
            <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="px-4 py-2.5 rounded-xl border text-xs">
            <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="px-4 py-2.5 rounded-xl border text-xs">
            <button class="px-4 py-2.5 bg-[#6F42C1] text-white font-bold rounded-xl text-xs">تصفية</button>
        </div>
    </form>

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b text-gray-500 font-bold">
                        <th class="py-4 px-6">رقم المعاملة</th>
                        <th class="py-4 px-6">المشترك</th>
                        <th class="py-4 px-6">بوابة الدفع</th>
                        <th class="py-4 px-6">المبلغ</th>
                        <th class="py-4 px-6">التاريخ</th>
                        <th class="py-4 px-6">الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($payments as $p)
                        <tr class="hover:bg-purple-50/20">
                            <td class="py-4 px-6 font-mono text-purple-700 font-bold">{{ $p->reference }}</td>
                            <td class="py-4 px-6">
                                <p class="font-bold">{{ $p->subscriber_name ?: ($p->user?->name ?? '—') }}</p>
                                <p class="font-mono text-gray-400">{{ $p->contract_number ?: '—' }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <span class="inline-flex bg-purple-50 text-purple-800 px-2.5 py-1 rounded-lg font-bold">{{ $p->gatewayLabel() }}</span>
                            </td>
                            <td class="py-4 px-6 font-mono text-emerald-700 font-black">{{ number_format($p->amount, 2) }} د.ل</td>
                            <td class="py-4 px-6 font-mono text-gray-400">{{ ($p->paid_at ?: $p->created_at)?->format('Y-m-d H:i') }}</td>
                            <td class="py-4 px-6">
                                <x-status-badge :status="$p->status === 'completed' ? 'completed' : ($p->status === 'pending' ? 'pending' : 'failed')" :label="$p->statusLabel()" />
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-10 text-center text-gray-400">لا توجد معاملات مالية.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $payments->links() }}</div>
    </div>
</div>
@endsection
