@extends('layouts.app')

@section('title', 'سجل العمليات')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="سجل العمليات والتدقيق (Audit Logs)" subtitle="متابعة التدقيق الأمني والعمليات الإدارية" />

    <!-- Filters & Search Form -->
    <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 text-right">
            <div>
                <label for="search" class="block text-[11px] font-semibold text-gray-500 mb-1">بحث</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="اسم، عملية، IP..."
                       class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
            </div>

            <div>
                <label for="action" class="block text-[11px] font-semibold text-gray-500 mb-1">نوع العملية</label>
                <select id="action" name="action" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                    <option value="">الجميع</option>
                    @foreach ($actions as $act)
                        <option value="{{ $act }}" @selected(request('action') === $act)>{{ $act }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="entity_type" class="block text-[11px] font-semibold text-gray-500 mb-1">الكيان</label>
                <select id="entity_type" name="entity_type" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                    <option value="">الجميع</option>
                    @foreach ($entityTypes as $ent)
                        <option value="{{ $ent }}" @selected(request('entity_type') === $ent)>{{ $ent }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="from_date" class="block text-[11px] font-semibold text-gray-500 mb-1">من تاريخ</label>
                <input type="date" id="from_date" name="from_date" value="{{ request('from_date') }}"
                       class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 bg-[#6F42C1] hover:bg-[#5B2BB8] text-white font-bold rounded-xl text-xs shadow-sm transition">
                    تصفية
                </button>
                @if (request()->anyFilled(['search', 'action', 'entity_type', 'from_date', 'to_date']))
                    <a href="{{ route('admin.audit-logs.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-xs transition">
                        إلغاء
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 font-bold">
                        <th class="py-4 px-6">المستخدم / الفاعل</th>
                        <th class="py-4 px-6">العملية</th>
                        <th class="py-4 px-6">الكيان (Entity)</th>
                        <th class="py-4 px-6">عنوان IP</th>
                        <th class="py-4 px-6">التاريخ والوقت</th>
                        <th class="py-4 px-6">التفاصيل</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-purple-50/20 transition">
                            <td class="py-4 px-6 font-bold text-gray-900">
                                {{ $log->user?->name ?? 'النظام / غير معروف' }}
                                @if ($log->user?->username)
                                    <span class="block text-[11px] font-mono text-purple-700 font-normal dir-ltr text-right">@ {{ $log->user->username }}</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 font-mono font-bold text-[11px]">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="py-4 px-6 font-mono text-gray-600">
                                {{ $log->entity_type }} #{{ $log->entity_id ?? '—' }}
                            </td>
                            <td class="py-4 px-6 font-mono text-gray-500 dir-ltr text-right">{{ $log->ip_address ?? '—' }}</td>
                            <td class="py-4 px-6 text-gray-500">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td class="py-4 px-6">
                                <a href="{{ route('admin.audit-logs.show', $log) }}"
                                   class="px-3 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 font-semibold rounded-lg transition text-[11px]">
                                    عرض التغييرات
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 px-6 text-center text-gray-400">لا توجد سجلات تدقيق مطابقة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
