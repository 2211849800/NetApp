@extends('layouts.app')

@section('title', 'الباقات')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="إدارة الباقات" subtitle="باقات الإنترنت" :action-url="route('admin.packages.create')" action-label="إضافة باقة" />
    <x-admin.flash />

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-right text-xs">
            <thead>
                <tr class="bg-gray-50 border-b text-gray-500 font-bold">
                    <th class="py-4 px-6">الاسم</th>
                    <th class="py-4 px-6">السعر</th>
                    <th class="py-4 px-6">الحصة</th>
                    <th class="py-4 px-6">المدة</th>
                    <th class="py-4 px-6">الحالة</th>
                    <th class="py-4 px-6">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($packages as $package)
                    <tr class="hover:bg-purple-50/20">
                        <td class="py-4 px-6 font-bold">{{ $package->name }}</td>
                        <td class="py-4 px-6">{{ number_format($package->price, 2) }} د.ل</td>
                        <td class="py-4 px-6">{{ $package->data_allowance ?? '—' }}</td>
                        <td class="py-4 px-6">{{ $package->duration_days }} يوم</td>
                        <td class="py-4 px-6">{{ $package->status?->labelAr() }}</td>
                        <td class="py-4 px-6">
                            <x-admin.resource-actions :item="$package" :edit-route="route('admin.packages.edit', $package)" :toggle-route="route('admin.packages.toggle-status', $package)" :show-route="route('admin.packages.show', $package)" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-10 text-center text-gray-400">لا توجد باقات.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
