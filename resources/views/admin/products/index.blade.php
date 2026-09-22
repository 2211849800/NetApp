@extends('layouts.app')

@section('title', 'إدارة المنتجات')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="إدارة المنتجات والأجهزة" subtitle="الراوترات والمعدات المتاحة للمشتركين" :action-url="route('admin.products.create')" action-label="إضافة منتج جديد" />
    <x-admin.flash />

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 font-bold">
                        <th class="py-4 px-6">المنتج</th>
                        <th class="py-4 px-6">السعر</th>
                        <th class="py-4 px-6">التوفر</th>
                        <th class="py-4 px-6">الحالة</th>
                        <th class="py-4 px-6">تاريخ الإنشاء</th>
                        <th class="py-4 px-6">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse ($products as $product)
                        <tr class="hover:bg-purple-50/20 transition">
                            <td class="py-4 px-6 font-bold text-gray-900 flex items-center gap-3">
                                @if ($product->image_path)
                                    <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="w-10 h-10 object-cover rounded-xl border border-gray-200">
                                @else
                                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-bold text-gray-900">{{ $product->name }}</p>
                                    <p class="text-[11px] text-gray-400 font-normal truncate max-w-xs">{{ $product->description }}</p>
                                </div>
                            </td>
                            <td class="py-4 px-6 font-bold text-purple-700">{{ number_format($product->price, 2) }} د.ل</td>
                            <td class="py-4 px-6">
                                @if ($product->is_available)
                                    <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-semibold">متاح</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 font-semibold">غير متاح</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @if ($product->status?->value === 'active')
                                    <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-semibold">نشط</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 font-semibold">معطل</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-gray-500">{{ $product->created_at?->format('Y-m-d') }}</td>
                            <td class="py-4 px-6">
                                <x-admin.resource-actions :item="$product" :edit-route="route('admin.products.edit', $product)" :toggle-route="route('admin.products.toggle-status', $product)" :show-route="route('admin.products.show', $product)" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 px-6 text-center text-gray-400">لا توجد منتجات مضافة بعد. ابدأ بإضافة منتج جديد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
