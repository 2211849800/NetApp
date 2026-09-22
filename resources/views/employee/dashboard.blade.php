@extends('layouts.app')

@section('title', 'لوحة تحكم الموظف')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">مرحباً، {{ auth()->user()->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">لوحة عمليات الموظف — بيانات مباشرة من النظام</p>
        </div>
    </div>

    <x-admin.flash />

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <a href="{{ route('staff.recharges.index', ['status' => 'pending']) }}" class="bg-white rounded-2xl border border-amber-100 p-6 shadow-sm hover:border-amber-300 transition">
            <p class="text-xs text-gray-400 font-medium">طلبات الشحن المعلقة</p>
            <p class="text-3xl font-black text-amber-600 mt-2">{{ number_format($stats['pending_recharges']) }}</p>
        </a>
        <a href="{{ route('staff.complaints.index', ['status' => 'open']) }}" class="bg-white rounded-2xl border border-rose-100 p-6 shadow-sm hover:border-rose-300 transition">
            <p class="text-xs text-gray-400 font-medium">التذاكر المفتوحة</p>
            <p class="text-3xl font-black text-rose-600 mt-2">{{ number_format($stats['open_tickets']) }}</p>
        </a>
        <a href="{{ route('staff.subscription-requests.index', ['status' => 'pending']) }}" class="bg-white rounded-2xl border border-purple-100 p-6 shadow-sm hover:border-purple-300 transition">
            <p class="text-xs text-gray-400 font-medium">طلبات اشتراك معلّقة</p>
            <p class="text-3xl font-black text-purple-700 mt-2">{{ number_format($stats['pending_subscriptions']) }}</p>
        </a>
        <div class="bg-white rounded-2xl border border-emerald-100 p-6 shadow-sm">
            <p class="text-xs text-gray-400 font-medium">العمليات المنجزة اليوم</p>
            <p class="text-3xl font-black text-emerald-600 mt-2">{{ number_format($stats['completed_today']) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <a href="{{ route('staff.subscribers.index') }}" class="bg-white rounded-2xl border p-5 hover:border-purple-200 shadow-sm">
            <h3 class="font-bold">استعلام المشتركين</h3>
            <p class="text-xs text-gray-400 mt-1">بحث برقم العقد والشحن وتغيير الباقة</p>
        </a>
        <a href="{{ route('staff.recharges.index') }}" class="bg-white rounded-2xl border p-5 hover:border-purple-200 shadow-sm">
            <h3 class="font-bold">طلبات الشحن</h3>
            <p class="text-xs text-gray-400 mt-1">قبول أو رفض طلبات الشحن</p>
        </a>
        <a href="{{ route('staff.payments.index') }}" class="bg-white rounded-2xl border p-5 hover:border-purple-200 shadow-sm">
            <h3 class="font-bold">سجل المدفوعات</h3>
            <p class="text-xs text-gray-400 mt-1">المعاملات عبر Lypay و OnePay والتحويل النقدي</p>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white rounded-3xl border shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b font-bold text-sm">أحدث طلبات الشحن</div>
            <div class="divide-y text-xs">
                @forelse ($latestRecharges as $item)
                    <a href="{{ route('staff.recharges.show', $item) }}" class="flex justify-between px-5 py-3 hover:bg-purple-50/40">
                        <span class="font-bold">{{ $item->subscriber_name ?: 'طلب #'.$item->id }}</span>
                        <span class="text-gray-400">{{ $item->statusLabel() }}</span>
                    </a>
                @empty
                    <p class="px-5 py-6 text-gray-400">لا توجد طلبات.</p>
                @endforelse
            </div>
        </div>
        <div class="bg-white rounded-3xl border shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b font-bold text-sm">أحدث التذاكر</div>
            <div class="divide-y text-xs">
                @forelse ($latestComplaints as $item)
                    <a href="{{ route('staff.complaints.show', $item) }}" class="flex justify-between px-5 py-3 hover:bg-purple-50/40">
                        <span class="font-bold truncate">{{ $item->subject }}</span>
                        <span class="text-gray-400 shrink-0 mr-2">{{ $item->statusLabel() }}</span>
                    </a>
                @empty
                    <p class="px-5 py-6 text-gray-400">لا توجد تذاكر.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl border shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b font-bold text-sm">عمليات اليوم</div>
        <div class="divide-y text-xs">
            @forelse ($todayOperations as $log)
                <div class="flex justify-between px-5 py-3">
                    <span class="font-bold">{{ $log->action }}</span>
                    <span class="text-gray-400">{{ $log->user?->name }} · {{ $log->created_at?->format('H:i') }}</span>
                </div>
            @empty
                <p class="px-5 py-6 text-gray-400">لا توجد عمليات مسجّلة اليوم بعد.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
