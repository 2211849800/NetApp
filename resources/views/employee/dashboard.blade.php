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

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
        <a href="{{ route('staff.subscribers.index') }}" class="bg-white rounded-2xl border border-emerald-100 p-6 shadow-sm hover:border-emerald-300 transition">
            <p class="text-xs text-gray-400 font-medium">رصيد الشحن المتاح</p>
            <p class="text-3xl font-black text-emerald-700 mt-2">{{ number_format($walletBalance, 2) }} <span class="text-sm">د.ل</span></p>
            <p class="text-xs text-gray-400 mt-1">متاح لاستخدامه في شحن المشتركين</p>
        </a>
        <a href="{{ route('staff.recharges.index') }}" class="bg-white rounded-2xl border border-amber-100 p-6 shadow-sm hover:border-amber-300 transition">
            <p class="text-xs text-gray-400 font-medium">طلبات الشحن المعلقة للفريق</p>
            <p class="text-3xl font-black text-amber-600 mt-2">{{ number_format($stats['pending_recharges']) }}</p>
        </a>
        <a href="{{ route('staff.complaints.index') }}" class="bg-white rounded-2xl border border-rose-100 p-6 shadow-sm hover:border-rose-300 transition">
            <p class="text-xs text-gray-400 font-medium">الشكاوى المفتوحة للفريق</p>
            <p class="text-3xl font-black text-rose-600 mt-2">{{ number_format($stats['open_tickets']) }}</p>
        </a>
        <a href="{{ route('staff.subscription-requests.index') }}" class="bg-white rounded-2xl border border-purple-100 p-6 shadow-sm hover:border-purple-300 transition">
            <p class="text-xs text-gray-400 font-medium">طلبات الاشتراك المعلّقة للفريق</p>
            <p class="text-3xl font-black text-purple-700 mt-2">{{ number_format($stats['pending_subscriptions']) }}</p>
        </a>
        <div class="bg-white rounded-2xl border border-emerald-100 p-6 shadow-sm">
            <p class="text-xs text-gray-400 font-medium">عملياتك الناجحة اليوم</p>
            <p class="text-3xl font-black text-emerald-600 mt-2">{{ number_format($stats['completed_today']) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <a href="{{ route('staff.subscribers.index') }}" class="bg-white rounded-2xl border p-5 hover:border-purple-200 shadow-sm">
            <h3 class="font-bold">استعلام المشتركين</h3>
            <p class="text-xs text-gray-400 mt-1">بحث برقم العقد والشحن وتغيير الباقة</p>
        </a>
        <a href="{{ route('staff.my-recharges.index') }}" class="bg-white rounded-2xl border border-emerald-100 p-5 hover:border-emerald-300 shadow-sm">
            <h3 class="font-bold">سجل شحناتي</h3>
            <p class="text-xs text-gray-400 mt-1">مراجعة المشتركين وعمليات الشحن التي نفذتها</p>
        </a>
        <a href="{{ route('staff.subscription-requests.index') }}" class="bg-white rounded-2xl border p-5 hover:border-purple-200 shadow-sm">
            <h3 class="font-bold">طلبات الاشتراك</h3>
            <p class="text-xs text-gray-400 mt-1">مراجعة طلبات الاشتراك الجديدة</p>
        </a>
        <a href="{{ route('staff.payments.index') }}" class="bg-white rounded-2xl border p-5 hover:border-purple-200 shadow-sm">
            <h3 class="font-bold">سجل المدفوعات</h3>
            <p class="text-xs text-gray-400 mt-1">المعاملات عبر Lypay و OnePay والتحويل النقدي</p>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a href="{{ route('staff.recharges.index') }}" class="flex items-center justify-between rounded-2xl border border-amber-200 bg-amber-50 p-5 hover:border-amber-300">
            <span class="font-semibold text-gray-800">طلبات شحن تجاوزت ٢٤ ساعة</span>
            <span class="text-xl font-bold text-amber-700">{{ number_format($stats['stale_recharges']) }}</span>
        </a>
        <a href="{{ route('staff.complaints.index') }}" class="flex items-center justify-between rounded-2xl border border-rose-200 bg-rose-50 p-5 hover:border-rose-300">
            <span class="font-semibold text-gray-800">شكاوى مفتوحة تجاوزت ٤٨ ساعة</span>
            <span class="text-xl font-bold text-rose-700">{{ number_format($stats['stale_tickets']) }}</span>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white rounded-3xl border shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b font-bold text-sm">أحدث طلبات الشحن التي تحتاج مراجعة</div>
            <div class="divide-y text-xs">
                @forelse ($latestRecharges as $item)
                    <a href="{{ route('staff.recharges.show', $item) }}" class="flex justify-between px-5 py-3 hover:bg-purple-50/40">
                        <span class="font-bold">{{ $item->subscriber_name ?: 'طلب #'.$item->id }}</span>
                        <span class="text-gray-400">{{ $item->statusLabel() }} · {{ $item->created_at?->diffForHumans() }}</span>
                    </a>
                @empty
                    <p class="px-5 py-6 text-gray-400">مافيش طلبات شحن تحتاج مراجعة حاليًا.</p>
                @endforelse
            </div>
        </div>
        <div class="bg-white rounded-3xl border shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b font-bold text-sm">أحدث الشكاوى المفتوحة</div>
            <div class="divide-y text-xs">
                @forelse ($latestComplaints as $item)
                    <a href="{{ route('staff.complaints.show', $item) }}" class="flex justify-between px-5 py-3 hover:bg-purple-50/40">
                        <span class="font-bold truncate">{{ $item->subject }}</span>
                        <span class="text-gray-400 shrink-0 mr-2">{{ $item->priorityLabel() }} · {{ $item->created_at?->diffForHumans() }}</span>
                    </a>
                @empty
                    <p class="px-5 py-6 text-gray-400">مافيش شكاوى مفتوحة حاليًا.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl border shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b font-bold text-sm">عملياتك اليوم</div>
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
