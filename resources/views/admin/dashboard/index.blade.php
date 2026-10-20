@extends('layouts.app')

@section('title', 'لوحة تحكم المدير')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="لوحة تحكم المدير" subtitle="نظرة عامة على أداء المنصة" />

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
        @foreach ([
            ['label' => 'إجمالي المشتركين', 'value' => $stats['total_subscribers'], 'color' => 'blue', 'icon' => 'users', 'url' => route('staff.subscribers.index'), 'detail' => 'عرض المشتركين'],
            ['label' => 'المشتركون النشطون', 'value' => $stats['active_subscribers'], 'color' => 'green', 'icon' => 'smile', 'url' => route('staff.subscribers.index'), 'detail' => 'عرض المشتركين'],
            ['label' => 'طلبات الشحن المعلّقة', 'value' => $stats['pending_recharges'], 'color' => 'orange', 'icon' => 'clock', 'url' => route('staff.recharges.index', ['status' => 'pending']), 'detail' => 'مراجعة الطلبات'],
            ['label' => 'الشكاوى المفتوحة', 'value' => $stats['open_complaints'], 'color' => 'amber', 'icon' => 'message', 'url' => route('staff.complaints.index'), 'detail' => 'عرض الشكاوى'],
            ['label' => 'إجمالي الموظفين', 'value' => $stats['total_employees'], 'color' => 'indigo', 'icon' => 'users', 'url' => route('admin.employees.index'), 'detail' => 'إدارة الموظفين'],
            ['label' => 'الباقات النشطة', 'value' => $stats['active_packages'], 'color' => 'violet', 'icon' => 'package', 'url' => route('admin.packages.index'), 'detail' => 'إدارة الباقات'],
        ] as $card)
            <a href="{{ $card['url'] }}"
               aria-label="{{ $card['label'] }}: {{ number_format($card['value']) }}. {{ $card['detail'] }}"
               class="group relative flex min-h-32 flex-col justify-between overflow-hidden rounded-2xl text-white shadow-sm transition duration-200 ease-out hover:-translate-y-1 hover:shadow-lg focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-blue-300 focus-visible:ring-offset-2 active:scale-[.99]
               {{ $card['color'] === 'blue' ? 'bg-[#4f79bc]' : '' }}
               {{ $card['color'] === 'green' ? 'bg-[#00a765]' : '' }}
               {{ $card['color'] === 'orange' ? 'bg-[#f39a0b]' : '' }}
               {{ $card['color'] === 'amber' ? 'bg-[#d98b08]' : '' }}
               {{ $card['color'] === 'indigo' ? 'bg-[#526fc0]' : '' }}
               {{ $card['color'] === 'violet' ? 'bg-[#7650b7]' : '' }}">
                <div class="relative flex flex-1 items-start justify-between gap-4 px-5 py-4 sm:px-6">
                    <div class="min-w-0 text-right">
                        <p class="text-2xl font-semibold leading-none tabular-nums">{{ number_format($card['value']) }}</p>
                        <p class="mt-3 text-sm font-bold leading-6 text-white/95">{{ $card['label'] }}</p>
                    </div>

                    <div class="shrink-0 text-white/35 transition duration-200 group-hover:scale-110 group-hover:text-white/55" aria-hidden="true">
                        @if ($card['icon'] === 'users')
                            <svg class="h-14 w-14" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11a4 4 0 1 0-3.9-4.9A5.5 5.5 0 0 1 13 10c0 .3 0 .6-.1.9.9.1 2 .1 3.1.1ZM8 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm0 2c-3.3 0-6 1.7-6 3.8V20h12v-3.2C14 14.7 11.3 13 8 13Zm8.5.1c-.6 0-1.2.1-1.8.2 1.4.9 2.3 2.1 2.3 3.5V20H22v-3.1c0-2.1-2.5-3.8-5.5-3.8Z"/></svg>
                        @elseif ($card['icon'] === 'smile')
                            <svg class="h-14 w-14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M8 14s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01"/></svg>
                        @elseif ($card['icon'] === 'clock')
                            <svg class="h-14 w-14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        @elseif ($card['icon'] === 'message')
                            <svg class="h-14 w-14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8A8.5 8.5 0 0 1 8.7 3.9a8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8v.5Z"/></svg>
                        @else
                            <svg class="h-14 w-14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m21 8-9-5-9 5v11h18V8ZM3 8l9 5 9-5M12 13v6"/></svg>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-center gap-2 bg-black/15 px-4 py-2 text-sm font-semibold text-white/95 transition-colors group-hover:bg-black/25">
                    <span>{{ $card['detail'] }}</span>
                    <svg class="h-4 w-4 transition-transform group-hover:-translate-x-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm1.7-11.7a1 1 0 0 0-1.4 1.4L11.6 9H7a1 1 0 1 0 0 2h4.6l-1.3 1.3a1 1 0 1 0 1.4 1.4l3-3a1 1 0 0 0 0-1.4l-3-3Z" clip-rule="evenodd"/></svg>
                </div>
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @can('manage_packages')
        <a href="{{ route('admin.packages.index') }}" class="bg-white rounded-2xl border border-gray-100 p-5 hover:border-purple-200 transition shadow-sm">
            <h3 class="font-bold text-gray-900">الباقات</h3>
            <p class="text-xs text-gray-400 mt-1">إدارة باقات الإنترنت</p>
        </a>
        @endcan
        @can('manage_employees')
        <a href="{{ route('admin.employees.index') }}" class="bg-white rounded-2xl border border-gray-100 p-5 hover:border-purple-200 transition shadow-sm">
            <h3 class="font-bold text-gray-900">الموظفين</h3>
            <p class="text-xs text-gray-400 mt-1">إنشاء وإدارة حسابات الموظفين</p>
        </a>
        @endcan
        @can('view_audit_logs')
        <a href="{{ route('admin.audit-logs.index') }}" class="bg-white rounded-2xl border border-gray-100 p-5 hover:border-purple-200 transition shadow-sm">
            <h3 class="font-bold text-gray-900">سجل العمليات</h3>
            <p class="text-xs text-gray-400 mt-1">مراجعة العمليات الإدارية</p>
        </a>
        @endcan
    </div>
</div>
@endsection
