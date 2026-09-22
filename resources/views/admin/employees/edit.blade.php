@extends('layouts.app')

@section('title', 'تعديل بيانات الموظف')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <x-admin.page-header title="تعديل حساب الموظف: {{ $employee->name }}" />
    <x-admin.errors />

    <div class="bg-white p-6 md:p-8 rounded-3xl border border-gray-100 shadow-sm">
        <form method="POST" action="{{ route('admin.employees.update', $employee) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5">الاسم الكامل</label>
                <input type="text" id="name" name="name" value="{{ old('name', $employee->name) }}" required
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
            </div>

            <div>
                <label for="username" class="block text-sm font-semibold text-gray-700 mb-1.5">اسم المستخدم</label>
                <input type="text" id="username" name="username" value="{{ old('username', $employee->username) }}" required
                       autocomplete="off"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-mono dir-ltr text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
            </div>

            <div>
                <label for="phone" class="block text-sm font-semibold text-gray-700 mb-1.5">رقم الهاتف</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $employee->phone) }}" required
                       autocomplete="off"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-mono dir-ltr text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">البريد الإلكتروني (اختياري)</label>
                <input type="email" id="email" name="email" value="{{ old('email', $employee->email) }}"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="role" class="block text-sm font-semibold text-gray-700 mb-1.5">الدور</label>
                    @php $currentRole = $employee->roles->first()?->name ?? 'employee'; @endphp
                    <select id="role" name="role" required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                        <option value="employee" @selected(old('role', $currentRole) === 'employee')>موظف (Employee)</option>
                        <option value="supervisor" @selected(old('role', $currentRole) === 'supervisor')>مشرف (Supervisor)</option>
                        @if ($canAssignAdmin ?? false)
                            <option value="admin" @selected(old('role', $currentRole) === 'admin')>مدير (Admin)</option>
                        @endif
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold text-gray-700 mb-1.5">حالة الحساب</label>
                    <select id="status" name="status" required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                        <option value="active" @selected(old('status', $employee->status?->value) === 'active')>نشط (Active)</option>
                        <option value="inactive" @selected(old('status', $employee->status?->value) === 'inactive')>معطل (Inactive)</option>
                        <option value="suspended" @selected(old('status', $employee->status?->value) === 'suspended')>موقوف (Suspended)</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('admin.employees.index') }}" class="text-xs font-semibold text-gray-500 hover:text-purple-700">
                    ← إلغاء والعودة
                </a>
                <button type="submit" class="px-6 py-3 bg-[#6F42C1] hover:bg-[#5B2BB8] text-white font-bold rounded-xl shadow-md transition">
                    حفظ البيانات
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
