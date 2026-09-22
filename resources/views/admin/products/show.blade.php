@extends('layouts.app')

@section('title', 'تفاصيل المنتج')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-admin.page-header title="تفاصيل المنتج: {{ $product->name }}" :action-url="route('admin.products.edit', $product)" action-label="تعديل المنتج" />
    <x-admin.flash />

    <div class="bg-white p-6 md:p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-b border-gray-100 pb-4">
            <div class="flex items-center gap-4">
                @if ($product->image_path)
                    <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="w-16 h-16 object-cover rounded-2xl border border-gray-200 shadow-sm">
                @else
                    <div class="w-16 h-16 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center shadow-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                @endif
                <div>
                    <h3 class="text-xl font-bold text-gray-900">{{ $product->name }}</h3>
                    <p class="text-xs text-gray-400 mt-1">تاريخ إضافة المنتج: {{ $product->created_at?->format('Y-m-d H:i') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if ($product->status?->value === 'active')
                    <span class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 font-semibold text-xs">نشط</span>
                @else
                    <span class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 font-semibold text-xs">معطل</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-center">
            <div class="bg-purple-50/50 p-4 rounded-2xl border border-purple-100">
                <p class="text-xs text-purple-600 font-semibold">السعر</p>
                <p class="text-2xl font-black text-purple-900 mt-1">{{ number_format($product->price, 2) }} د.ل</p>
            </div>
            <div class="bg-purple-50/50 p-4 rounded-2xl border border-purple-100">
                <p class="text-xs text-purple-600 font-semibold">حالة التوفر</p>
                <p class="text-xl font-black text-purple-900 mt-1">{{ $product->is_available ? 'متاح في المخزون' : 'غير متاح حالياً' }}</p>
            </div>
        </div>

        @if ($product->description)
            <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                <h4 class="text-xs font-bold text-gray-500 mb-2">الوصف</h4>
                <p class="text-sm text-gray-700 leading-relaxed">{{ $product->description }}</p>
            </div>
        @endif

        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
            <a href="{{ route('admin.products.index') }}" class="text-xs font-semibold text-gray-500 hover:text-purple-700">
                ← العودة إلى قائمة المنتجات
            </a>
            <form method="POST" action="{{ route('admin.products.toggle-status', $product) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition {{ $product->status?->value === 'active' ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    {{ $product->status?->value === 'active' ? 'تعطيل المنتج' : 'تفعيل المنتج' }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
