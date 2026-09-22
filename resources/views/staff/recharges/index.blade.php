@extends('layouts.app')

@section('title', 'طلبات الشحن')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="طلبات الشحن" subtitle="مراجعة طلبات شحن الباقات وقبولها أو رفضها مع تحديث حالة المشترك" />
    <x-admin.flash />
    <x-admin.errors />

    @if (($counts['pending'] ?? 0) > 0)
        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 rounded-3xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-purple-600 text-white flex items-center justify-center shadow-md shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-purple-950">إشعارات تحويلات مصرفية وطلبات شحن معلّقة ({{ $counts['pending'] }} طلب)</h4>
                    <p class="text-xs text-purple-700">عند تأكيدك للتحويل وشحن الحساب، سيتم قيد المبلغ تلقائياً في <strong>"رصيدك الحسابي"</strong> وإقفال المعاملة باسمك لمنع التزوير أو التكرار.</p>
                </div>
            </div>
            <a href="{{ route('staff.recharges.index', ['status' => 'pending']) }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl shadow transition-all whitespace-nowrap">
                عرض الطلبات المعلّقة فقط
            </a>
        </div>
    @endif


    <div class="flex flex-wrap gap-2">
        @foreach (['' => 'الكل', 'pending' => 'معلقة', 'approved' => 'مقبولة', 'rejected' => 'مرفوضة'] as $value => $label)
            <a href="{{ route('staff.recharges.index', array_filter(['status' => $value])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold border {{ ($status ?? '') === $value ? 'bg-[#6F42C1] text-white border-transparent' : 'bg-white text-gray-600 border-gray-200 hover:border-purple-200' }}">
                {{ $label }}
                @if ($value === '') ({{ $counts['all'] }})
                @elseif(isset($counts[$value])) ({{ $counts[$value] }})
                @endif
            </a>
        @endforeach
    </div>

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b text-gray-500 font-bold">
                        <th class="py-4 px-6">الطلب</th>
                        <th class="py-4 px-6">المشترك</th>
                        <th class="py-4 px-6">الباقة</th>
                        <th class="py-4 px-6">المبلغ</th>
                        <th class="py-4 px-6">طريقة الدفع</th>
                        <th class="py-4 px-6">الحالة</th>
                        <th class="py-4 px-6">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($requests as $item)
                        <tr class="hover:bg-purple-50/20">
                            <td class="py-4 px-6 font-mono text-purple-700 font-bold">#RR-{{ $item->id }}</td>
                            <td class="py-4 px-6">
                                <p class="font-bold text-gray-900">{{ $item->subscriber_name ?: ($item->user?->name ?? '—') }}</p>
                                <p class="font-mono text-gray-400 mt-0.5">{{ $item->contract_number ?: '—' }}</p>
                            </td>
                            <td class="py-4 px-6">{{ $item->package_name ?: $item->package_id ?: '—' }}</td>
                            <td class="py-4 px-6 font-mono font-bold text-emerald-700">{{ $item->amount !== null ? number_format($item->amount, 2).' د.ل' : '—' }}</td>
                            <td class="py-4 px-6">{{ $item->paymentMethodLabel() }}</td>
                            <td class="py-4 px-6">
                                <x-status-badge :status="$item->status === 'approved' ? 'completed' : ($item->status === 'pending' ? 'pending' : 'failed')" :label="$item->statusLabel()" />
                            </td>
                            <td class="py-4 px-6">
                                <a href="{{ route('staff.recharges.show', $item) }}" class="text-purple-700 font-bold">معاينة</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-10 text-center text-gray-400">لا توجد طلبات شحن.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $requests->links() }}</div>
    </div>
</div>
@endsection
