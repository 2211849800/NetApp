@extends('layouts.app')

@section('title', 'معاينة طلب اشتراك')

@section('content')
<div class="space-y-6 max-w-3xl">
    <x-admin.page-header title="طلب اشتراك جديد" :subtitle="$requestItem->applicant_name" />
    <x-admin.flash />
    <x-admin.errors />

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 grid grid-cols-2 gap-4 text-sm">
        <div><p class="text-xs text-gray-400">الاسم</p><p class="font-bold">{{ $requestItem->applicant_name }}</p></div>
        <div><p class="text-xs text-gray-400">الرقم الوطني</p><p class="font-mono">{{ $requestItem->national_id ?: '—' }}</p></div>
        <div><p class="text-xs text-gray-400">الهاتف</p><p class="font-mono">{{ $requestItem->phone }}</p></div>
        <div><p class="text-xs text-gray-400">البريد</p><p>{{ $requestItem->email ?: '—' }}</p></div>
        <div><p class="text-xs text-gray-400">المدينة</p><p>{{ $requestItem->city ?: '—' }}</p></div>
        <div><p class="text-xs text-gray-400">الباقة المطلوبة</p><p class="font-bold">{{ $requestItem->package_name ?: $requestItem->package_id }}</p></div>
        <div class="col-span-2"><p class="text-xs text-gray-400">العنوان</p><p>{{ $requestItem->address ?: '—' }}</p></div>
        <div><p class="text-xs text-gray-400">الحالة</p><p class="font-bold">{{ $requestItem->statusLabel() }}</p></div>
        @if ($requestItem->user)
            <div><p class="text-xs text-gray-400">حساب المستخدم</p><p class="font-mono">{{ $requestItem->user->username }} / {{ $requestItem->user->contract_number }}</p></div>
        @endif
    </div>

    @if ($requestItem->isPending() || $requestItem->status === 'under_review')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @can('manage_subscription_requests')
            <form method="POST" action="{{ route('staff.subscription-requests.approve', $requestItem) }}" class="bg-white rounded-3xl border border-emerald-100 p-5 space-y-3">
                @csrf
                <p class="font-bold text-emerald-800 text-sm">قبول وإنشاء حساب</p>
                <p class="text-[11px] text-gray-500">سيتم إنشاء مستخدم في جدول users وتعيين رقم عقد وتسجيله في مزود المشتركين.</p>
                <textarea name="internal_notes" rows="3" class="w-full rounded-xl border text-xs px-3 py-2" placeholder="ملاحظات داخلية"></textarea>
                <button class="w-full py-2.5 bg-emerald-600 text-white font-bold rounded-xl text-xs">قبول الطلب</button>
            </form>
            <form method="POST" action="{{ route('staff.subscription-requests.reject', $requestItem) }}" class="bg-white rounded-3xl border border-rose-100 p-5 space-y-3">
                @csrf
                <p class="font-bold text-rose-800 text-sm">رفض الطلب</p>
                <textarea name="internal_notes" rows="3" required class="w-full rounded-xl border text-xs px-3 py-2" placeholder="سبب الرفض"></textarea>
                <button class="w-full py-2.5 bg-rose-600 text-white font-bold rounded-xl text-xs">رفض</button>
            </form>
            @endcan
        </div>
    @elseif ($requestItem->internal_notes)
        <div class="bg-white rounded-3xl border p-5 text-xs text-gray-600">{{ $requestItem->internal_notes }}</div>
    @endif

    <a href="{{ route('staff.subscription-requests.index') }}" class="inline-block text-xs font-bold text-purple-700">العودة للقائمة</a>
</div>
@endsection
