@extends('layouts.app')

@section('title', 'طلبات الاشتراك')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="طلبات الاشتراك" subtitle="طلبات الاشتراك الجديدة المقدمة من المواطنين" />
    <x-admin.flash />
    <x-admin.errors />

    <div class="flex flex-wrap gap-2">
        @foreach (['' => 'الكل', 'pending' => 'معلقة', 'under_review' => 'قيد المراجعة', 'approved' => 'مقبولة', 'rejected' => 'مرفوضة'] as $value => $label)
            <a href="{{ route('staff.subscription-requests.index', array_filter(['status' => $value])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold border {{ ($status ?? '') === $value ? 'bg-[#6F42C1] text-white border-transparent' : 'bg-white text-gray-600 border-gray-200' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b text-gray-500 font-bold">
                        <th class="py-4 px-6">مقدم الطلب</th>
                        <th class="py-4 px-6">الرقم الوطني</th>
                        <th class="py-4 px-6">الهاتف</th>
                        <th class="py-4 px-6">المدينة</th>
                        <th class="py-4 px-6">الباقة</th>
                        <th class="py-4 px-6">الحالة</th>
                        <th class="py-4 px-6">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($requests as $item)
                        <tr class="hover:bg-purple-50/20">
                            <td class="py-4 px-6 font-bold text-gray-900">{{ $item->applicant_name }}</td>
                            <td class="py-4 px-6 font-mono">{{ $item->national_id ?: '—' }}</td>
                            <td class="py-4 px-6 font-mono dir-ltr text-right">{{ $item->phone }}</td>
                            <td class="py-4 px-6">{{ $item->city ?: '—' }}</td>
                            <td class="py-4 px-6">{{ $item->package_name ?: $item->package_id ?: '—' }}</td>
                            <td class="py-4 px-6">
                                <x-status-badge :status="$item->status === 'approved' ? 'completed' : ($item->status === 'rejected' ? 'failed' : 'pending')" :label="$item->statusLabel()" />
                            </td>
                            <td class="py-4 px-6">
                                <a href="{{ route('staff.subscription-requests.show', $item) }}" class="text-purple-700 font-bold">معاينة</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-10 text-center text-gray-400">لا توجد طلبات اشتراك.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $requests->links() }}</div>
    </div>
</div>
@endsection
