@extends('layouts.app')

@section('title', 'سجل شحناتي')

@section('content')
<div class="space-y-6" dir="rtl">
    <x-admin.page-header title="سجل شحناتي" subtitle="المشتركون وعمليات الشحن التي نفذتها" />

    <form method="GET" action="{{ route('staff.my-recharges.index') }}" class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex flex-col sm:flex-row gap-3">
        <input name="q" value="{{ $query }}" class="flex-1 px-4 py-3 rounded-xl border border-gray-200 text-sm" placeholder="ابحث باسم المشترك أو رقم العقد أو رقم العملية">
        <button class="px-6 py-3 rounded-xl bg-[#6F42C1] text-white font-bold text-sm">بحث</button>
        @if($query !== '')<a href="{{ route('staff.my-recharges.index') }}" class="px-5 py-3 rounded-xl bg-gray-100 text-gray-700 font-bold text-sm text-center">مسح</a>@endif
    </form>

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead><tr class="bg-gray-50 border-b text-gray-500 text-xs font-bold">
                    <th class="py-4 px-5">المشترك</th><th class="py-4 px-5">رقم العقد</th><th class="py-4 px-5">الباقة</th><th class="py-4 px-5">المبلغ</th><th class="py-4 px-5">رقم العملية</th><th class="py-4 px-5">تاريخ الشحن</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-purple-50/30">
                            <td class="py-4 px-5 font-bold text-gray-900">{{ $payment->subscriber_name ?: '—' }}</td>
                            <td class="py-4 px-5 font-mono text-purple-700" dir="ltr">{{ $payment->contract_number }}</td>
                            <td class="py-4 px-5">{{ $packages[$payment->package_id]['name'] ?? $payment->package_id ?? '—' }}</td>
                            <td class="py-4 px-5 font-mono font-bold text-emerald-700">{{ number_format((float) $payment->amount, 2) }} د.ل</td>
                            <td class="py-4 px-5 font-mono text-xs text-gray-500">{{ $payment->reference }}</td>
                            <td class="py-4 px-5 font-mono text-xs text-gray-500" dir="ltr">{{ $payment->paid_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-12 text-center text-gray-400">لا توجد عمليات شحن مسجلة لك حتى الآن.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())<div class="p-5 border-t border-gray-100">{{ $payments->links() }}</div>@endif
    </div>
</div>
@endsection
