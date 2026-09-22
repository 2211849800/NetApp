@extends('layouts.app')

@section('title', 'تعديل صلاحيات الدور')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <x-admin.page-header title="تعديل صلاحيات الدور: {{ $role->display_name ?? $role->name }}" />
    <x-admin.errors />

    <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="space-y-6">
        @csrf
        @method('PUT')

        @php
            $assignedPermissionNames = $role->permissions->pluck('name')->toArray();
        @endphp

        <div class="space-y-6">
            @foreach ($permissions as $group => $groupPermissions)
                <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4 text-right">
                    <h3 class="font-bold text-sm text-purple-900 border-b border-gray-100 pb-2">
                        مجموعة: {{ $group ?? 'عام' }}
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach ($groupPermissions as $perm)
                            <label class="flex items-center gap-3 p-3 rounded-2xl border border-gray-100 hover:bg-purple-50/40 cursor-pointer transition">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                                       @checked(in_array($perm->name, old('permissions', $assignedPermissionNames)))
                                       class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500 border-gray-300">
                                <div>
                                    <p class="font-bold text-xs text-gray-800">{{ $perm->display_name ?? $perm->name }}</p>
                                    <p class="text-[10px] text-gray-400 font-mono mt-0.5 dir-ltr text-right">{{ $perm->name }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex items-center justify-between bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
            <a href="{{ route('admin.roles.index') }}" class="text-xs font-semibold text-gray-500 hover:text-purple-700">
                ← إلغاء والعودة
            </a>
            <button type="submit" class="px-8 py-3 bg-[#6F42C1] hover:bg-[#5B2BB8] text-white font-bold rounded-xl shadow-md transition text-xs">
                حفظ تعديلات الصلاحيات
            </button>
        </div>
    </form>
</div>
@endsection
