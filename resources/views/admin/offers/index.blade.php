@extends('layouts.app')

@section('title', 'إدارة العروض والخصومات')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="إدارة العروض الترويجية" subtitle="خصومات وحملات المشتركين" :action-url="route('admin.offers.create')" action-label="إضافة عرض جديد" />
    <x-admin.flash />

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 font-bold">
                        <th class="py-4 px-6">اسم العرض</th>
                        <th class="py-4 px-6">نسبة الخصم</th>
                        <th class="py-4 px-6">من تاريخ</th>
                        <th class="py-4 px-6">إلى تاريخ</th>
                        <th class="py-4 px-6">الحالة</th>
                        <th class="py-4 px-6">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse ($offers as $offer)
                        <tr class="hover:bg-purple-50/20 transition">
                            <td class="py-4 px-6 font-bold text-gray-900">
                                <p class="font-bold text-gray-900">{{ $offer->name }}</p>
                                <p class="text-[11px] text-gray-400 font-normal truncate max-w-xs">{{ $offer->description }}</p>
                            </td>
                            <td class="py-4 px-6 font-bold text-purple-700">{{ $offer->discount_percent ? $offer->discount_percent . '%' : '—' }}</td>
                            <td class="py-4 px-6 text-gray-600">{{ \Carbon\Carbon::parse($offer->starts_at)->format('Y-m-d') }}</td>
                            <td class="py-4 px-6 text-gray-600">{{ \Carbon\Carbon::parse($offer->ends_at)->format('Y-m-d') }}</td>
                            <td class="py-4 px-6">
                                @if ($offer->status?->value === 'active')
                                    <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-semibold">نشط</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 font-semibold">معطل</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                <x-admin.resource-actions :item="$offer" :edit-route="route('admin.offers.edit', $offer)" :toggle-route="route('admin.offers.toggle-status', $offer)" :show-route="route('admin.offers.show', $offer)" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 px-6 text-center text-gray-400">لا توجد عروض مضافة بعد. ابدأ بإضافة عرض جديد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
