@extends('layouts.app')

@section('title', 'دليل الصلاحيات')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="دليل الصلاحيات المسجلة في النظام" subtitle="قائمة كاملة بجميع الصلاحيات ومجموعاتها الحالية" />

    <div class="space-y-6">
        @foreach ($permissionsGrouped as $group => $perms)
            <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="font-bold text-base text-gray-900">مجموعة الصلاحيات: <span class="text-purple-700">{{ $group }}</span></h3>
                    <span class="px-3 py-1 rounded-xl bg-purple-50 text-purple-700 font-bold text-xs">
                        {{ $perms->count() }} صلاحيات
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($perms as $perm)
                        <div class="p-4 rounded-2xl bg-gray-50/70 border border-gray-100 space-y-1">
                            <h4 class="font-bold text-xs text-gray-900">{{ $perm->display_name ?? $perm->name }}</h4>
                            <p class="font-mono text-[11px] text-purple-700 dir-ltr text-right font-semibold">{{ $perm->name }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
