@extends('layouts.app')

@section('title', 'باقات IPTV')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="إدارة باقات IPTV" subtitle="اشتراكات البث والتلفزيون" :action-url="route('admin.iptv-packages.create')" action-label="إضافة باقة IPTV جديدة" />
    <x-admin.flash />

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 font-bold">
                        <th class="py-4 px-6">اسم الباقة</th>
                        <th class="py-4 px-6">السعر</th>
                        <th class="py-4 px-6">المدة</th>
                        <th class="py-4 px-6">الحالة</th>
                        <th class="py-4 px-6">تاريخ الإنشاء</th>
                        <th class="py-4 px-6">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse ($iptvPackages as $iptv)
                        <tr class="hover:bg-purple-50/20 transition">
                            <td class="py-4 px-6 font-bold text-gray-900">
                                <p class="font-bold text-gray-900">{{ $iptv->name }}</p>
                                <p class="text-[11px] text-gray-400 font-normal truncate max-w-xs">{{ $iptv->description }}</p>
                            </td>
                            <td class="py-4 px-6 font-bold text-purple-700">{{ number_format($iptv->price, 2) }} د.ل</td>
                            <td class="py-4 px-6 text-gray-600">{{ $iptv->duration_days }} يوم</td>
                            <td class="py-4 px-6">
                                @if ($iptv->status?->value === 'active')
                                    <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-semibold">نشط</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 font-semibold">معطل</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-gray-500">{{ $iptv->created_at?->format('Y-m-d') }}</td>
                            <td class="py-4 px-6">
                                <x-admin.resource-actions :item="$iptv" :edit-route="route('admin.iptv-packages.edit', $iptv)" :toggle-route="route('admin.iptv-packages.toggle-status', $iptv)" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 px-6 text-center text-gray-400">لا توجد باقات IPTV مضافة بعد. ابدأ بإضافة باقة جديدة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
