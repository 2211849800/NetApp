@extends('layouts.app')

@section('title', 'تفاصيل سجل العملية')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-admin.page-header title="تفاصيل سجل التدقيق #{{ $auditLog->id }}" />

    <div class="bg-white p-6 md:p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6 text-right">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pb-4 border-b border-gray-100 text-xs">
            <div>
                <p class="text-gray-400 font-semibold">المستخدم الفاعل</p>
                <p class="font-bold text-gray-900 mt-1">{{ $auditLog->user?->name ?? 'النظام' }} ({{ $auditLog->user?->username ?? 'System' }})</p>
            </div>
            <div>
                <p class="text-gray-400 font-semibold">العملية المنفذة</p>
                <p class="font-mono text-purple-700 font-bold mt-1 text-sm">{{ $auditLog->action }}</p>
            </div>
            <div>
                <p class="text-gray-400 font-semibold">الكيان المتأثر</p>
                <p class="font-mono text-gray-800 font-bold mt-1">{{ $auditLog->entity_type }} (ID: {{ $auditLog->entity_id ?? '—' }})</p>
            </div>
            <div>
                <p class="text-gray-400 font-semibold">تاريخ ووقت التنفيذ</p>
                <p class="font-mono text-gray-800 font-bold mt-1">{{ $auditLog->created_at?->format('Y-m-d H:i:s') }}</p>
            </div>
            <div>
                <p class="text-gray-400 font-semibold">عنوان IP</p>
                <p class="font-mono text-gray-800 font-bold dir-ltr text-right mt-1">{{ $auditLog->ip_address ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-400 font-semibold">نتيجة العملية</p>
                <span class="inline-block mt-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-bold text-xs">
                    {{ $auditLog->result ?? 'success' }}
                </span>
            </div>
        </div>

        <!-- Values Comparison -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-bold text-xs text-rose-600 mb-2">القيم القديمة (Before / Old Values)</h4>
                <pre class="bg-gray-900 text-rose-300 p-4 rounded-2xl text-xs font-mono overflow-x-auto dir-ltr text-left border border-gray-800">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '{}' }}</pre>
            </div>

            <div>
                <h4 class="font-bold text-xs text-emerald-600 mb-2">القيم الجديدة (After / New Values)</h4>
                <pre class="bg-gray-900 text-emerald-300 p-4 rounded-2xl text-xs font-mono overflow-x-auto dir-ltr text-left border border-gray-800">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '{}' }}</pre>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
            <a href="{{ route('admin.audit-logs.index') }}" class="text-xs font-semibold text-purple-600 hover:text-purple-800">
                ← العودة لسجل العمليات
            </a>
        </div>
    </div>
</div>
@endsection
