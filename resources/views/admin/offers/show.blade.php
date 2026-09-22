@extends('layouts.app')

@section('title', 'تفاصيل العرض')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-admin.page-header title="تفاصيل العرض: {{ $offer->name }}" :action-url="route('admin.offers.edit', $offer)" action-label="تعديل العرض" />
    <x-admin.flash />

    <div class="bg-white p-6 md:p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
            <div>
                <h3 class="text-xl font-bold text-gray-900">{{ $offer->name }}</h3>
                <p class="text-xs text-gray-400 mt-1">تاريخ إضافة العرض: {{ $offer->created_at?->format('Y-m-d H:i') }}</p>
            </div>
            <div>
                @if ($offer->status?->value === 'active')
                    <span class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 font-semibold text-xs">نشط</span>
                @else
                    <span class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 font-semibold text-xs">معطل</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
            <div class="bg-purple-50/50 p-4 rounded-2xl border border-purple-100">
                <p class="text-xs text-purple-600 font-semibold">نسبة الخصم</p>
                <p class="text-2xl font-black text-purple-900 mt-1">{{ $offer->discount_percent ? $offer->discount_percent . '%' : '—' }}</p>
            </div>
            <div class="bg-purple-50/50 p-4 rounded-2xl border border-purple-100">
                <p class="text-xs text-purple-600 font-semibold">يبدأ في</p>
                <p class="text-lg font-bold text-purple-900 mt-1">{{ \Carbon\Carbon::parse($offer->starts_at)->format('Y-m-d') }}</p>
            </div>
            <div class="bg-purple-50/50 p-4 rounded-2xl border border-purple-100">
                <p class="text-xs text-purple-600 font-semibold">ينتهي في</p>
                <p class="text-lg font-bold text-purple-900 mt-1">{{ \Carbon\Carbon::parse($offer->ends_at)->format('Y-m-d') }}</p>
            </div>
        </div>

        @if ($offer->description)
            <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                <h4 class="text-xs font-bold text-gray-500 mb-2">الوصف</h4>
                <p class="text-sm text-gray-700 leading-relaxed">{{ $offer->description }}</p>
            </div>
        @endif

        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
            <a href="{{ route('admin.offers.index') }}" class="text-xs font-semibold text-gray-500 hover:text-purple-700">
                ← العودة إلى قائمة العروض
            </a>
            <form method="POST" action="{{ route('admin.offers.toggle-status', $offer) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition {{ $offer->status?->value === 'active' ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    {{ $offer->status?->value === 'active' ? 'تعطيل العرض' : 'تفعيل العرض' }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
