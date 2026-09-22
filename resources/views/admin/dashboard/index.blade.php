@extends('layouts.app')

@section('title', 'لوحة تحكم المدير')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="لوحة تحكم المدير" subtitle="نظرة عامة على أداء المنصة" />

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach ([
            ['label' => 'إجمالي المشتركين', 'value' => $stats['total_subscribers'], 'color' => 'purple'],
            ['label' => 'المشتركون النشطون', 'value' => $stats['active_subscribers'], 'color' => 'emerald'],
            ['label' => 'طلبات شحن معلّقة', 'value' => $stats['pending_recharges'], 'color' => 'amber'],
            ['label' => 'شكاوى مفتوحة', 'value' => $stats['open_complaints'], 'color' => 'rose'],
            ['label' => 'إجمالي الموظفين', 'value' => $stats['total_employees'], 'color' => 'indigo'],
            ['label' => 'الباقات النشطة', 'value' => $stats['active_packages'], 'color' => 'violet'],
        ] as $card)
            <div class="bg-white rounded-2xl border border-purple-100 p-6 shadow-sm">
                <p class="text-xs text-gray-400 font-medium">{{ $card['label'] }}</p>
                <p class="text-3xl font-black text-gray-900 mt-2">{{ number_format($card['value']) }}</p>
            </div>
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
