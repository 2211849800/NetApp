@extends('layouts.app')

@section('title', 'سجل المدفوعات')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
        <div>
            <h2 class="text-xl font-black text-gray-900">سجل المدفوعات والمعاملات</h2>
            <p class="text-xs text-gray-400 mt-1">تتبع المدفوعات الإلكترونية عبر Lypay و OnePay والتحويلات البنكية</p>
        </div>
        <button class="px-4 py-2.5 bg-[#6F42C1] hover:bg-[#5B2BB8] text-white font-bold rounded-xl text-xs shadow-md transition">
            <span class="inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                تصدير تقرير الإيرادات
            </span>
        </button>
    </div>

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 font-bold">
                        <th class="py-4 px-6">رقم المعاملة</th>
                        <th class="py-4 px-6">المشترك</th>
                        <th class="py-4 px-6">بوابة الدفع</th>
                        <th class="py-4 px-6">المبلغ</th>
                        <th class="py-4 px-6">التاريخ</th>
                        <th class="py-4 px-6">الحالة</th>
                        <th class="py-4 px-6">تفاصيل</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @foreach($payments as $p)
                    <tr class="hover:bg-purple-50/20 transition">
                        <td class="py-4 px-6 font-mono text-purple-700 font-bold">{{ $p['id'] }}</td>
                        <td class="py-4 px-6 font-bold text-gray-900">{{ $p['subscriber'] }}</td>
                        <td class="py-4 px-6 font-medium text-gray-700">
                            <span class="inline-flex items-center gap-1 bg-purple-50 text-purple-800 px-2.5 py-1 rounded-lg font-bold">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg>
                                {{ $p['gateway'] }}
                            </span>
                        </td>
                        <td class="py-4 px-6 font-mono text-emerald-700 font-black text-sm">{{ $p['amount'] }}</td>
                        <td class="py-4 px-6 text-gray-400 font-mono text-[11px]">{{ $p['date'] }}</td>
                        <td class="py-4 px-6">
                            <x-status-badge :status="$p['status'] == 'تم بنجاح' ? 'completed' : 'pending'" :label="$p['status']" />
                        </td>
                        <td class="py-4 px-6">
                            <button class="text-purple-600 hover:text-purple-900 font-bold text-xs">عرض الإيصال</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
