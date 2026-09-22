@extends('layouts.app')

@section('title', 'معاينة طلب شحن')

@section('content')
<div class="space-y-6 max-w-3xl">
    <x-admin.page-header title="معاينة طلب الشحن #RR-{{ $requestItem->id }}" :subtitle="$requestItem->subscriber_name" />
    <x-admin.flash />
    <x-admin.errors />

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-4 text-sm">
        <div class="grid grid-cols-2 gap-4">
            <div><p class="text-xs text-gray-400">رقم العقد</p><p class="font-mono font-bold">{{ $requestItem->contract_number ?: '—' }}</p></div>
            <div><p class="text-xs text-gray-400">الحالة</p><p class="font-bold">{{ $requestItem->statusLabel() }}</p></div>
            <div><p class="text-xs text-gray-400">الباقة</p><p class="font-bold">{{ $requestItem->package_name }} ({{ $requestItem->package_id }})</p></div>
            <div><p class="text-xs text-gray-400">المبلغ</p><p class="font-bold">{{ $requestItem->amount !== null ? number_format($requestItem->amount, 2).' د.ل' : '—' }}</p></div>
            <div><p class="text-xs text-gray-400">طريقة الدفع</p><p class="font-bold">{{ $requestItem->paymentMethodLabel() }}</p></div>
            <div><p class="text-xs text-gray-400">تاريخ الطلب</p><p class="font-mono">{{ $requestItem->created_at?->format('Y-m-d H:i') }}</p></div>
        </div>
        @if ($requestItem->notes)
            <div class="rounded-2xl bg-gray-50 p-4 text-xs text-gray-600">{{ $requestItem->notes }}</div>
        @endif
        @if ($requestItem->processor)
            <p class="text-xs text-gray-400">عولج بواسطة {{ $requestItem->processor->name }} في {{ $requestItem->processed_at?->format('Y-m-d H:i') }}</p>
        @endif
    </div>

    @if ($requestItem->isPending())
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @can('approve_recharges')
            <form method="POST" action="{{ route('staff.recharges.approve', $requestItem) }}" class="bg-white rounded-3xl border border-emerald-100 p-5 space-y-3">
                @csrf
                <p class="font-bold text-emerald-800 text-sm">قبول الطلب</p>
                <textarea name="notes" rows="3" class="w-full rounded-xl border border-gray-200 text-xs px-3 py-2" placeholder="ملاحظات داخلية (اختياري)"></textarea>
                <button class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs">قبول وتحديث المشترك</button>
            </form>
            @endcan
            @can('reject_recharges')
            <form method="POST" action="{{ route('staff.recharges.reject', $requestItem) }}" class="bg-white rounded-3xl border border-rose-100 p-5 space-y-3">
                @csrf
                <p class="font-bold text-rose-800 text-sm">رفض الطلب</p>
                <textarea name="notes" rows="3" required class="w-full rounded-xl border border-gray-200 text-xs px-3 py-2" placeholder="سبب الرفض"></textarea>
                <button class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs">رفض الطلب</button>
            </form>
            @endcan
        </div>
    @endif

    <a href="{{ route('staff.recharges.index') }}" class="inline-block text-xs font-bold text-purple-700">العودة للقائمة</a>
</div>
@endsection
