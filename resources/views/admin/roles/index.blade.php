@extends('layouts.app')

@section('title', 'إدارة الأدوار والصلاحيات')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="إدارة الأدوار والصلاحيات" subtitle="تحديد صلاحيات الأدوار المتاحة في النظام (Admin, Supervisor, Employee)" />
    <x-admin.flash />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach ($roles as $role)
            <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-lg text-gray-900">{{ $role->display_name ?? $role->name }}</h3>
                        <span class="px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 font-mono font-bold text-xs">
                            {{ $role->name }}
                        </span>
                    </div>

                    <p class="text-xs text-gray-400 mt-2">
                        إجمالي الصلاحيات المسندة: <span class="font-bold text-purple-700">{{ $role->permissions->count() }}</span> صلاحية
                    </p>

                    <div class="mt-4 flex flex-wrap gap-1.5">
                        @forelse ($role->permissions->take(6) as $perm)
                            <span class="px-2 py-0.5 rounded-md bg-gray-100 text-gray-600 text-[11px]">
                                {{ $perm->name }}
                            </span>
                        @empty
                            <span class="text-xs text-gray-400">لا توجد صلاحيات مخصصة</span>
                        @endforelse
                        @if ($role->permissions->count() > 6)
                            <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-600 text-[11px] font-bold">
                                +{{ $role->permissions->count() - 6 }} أخرى
                            </span>
                        @endif
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100">
                    @if ($role->name === 'admin')
                        <p class="text-[11px] text-gray-400 text-center font-medium">صلاحيات المدير ثابتة وكاملة تلقائياً.</p>
                    @else
                        <a href="{{ route('admin.roles.edit', $role) }}"
                           class="block w-full py-2.5 text-center bg-[#6F42C1] hover:bg-[#5B2BB8] text-white font-bold text-xs rounded-xl shadow-sm transition">
                            تعديل الصلاحيات
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
