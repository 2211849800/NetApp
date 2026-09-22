@extends('layouts.app')

@section('title', 'الملف الشخصي للمدير')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-admin.page-header title="إدارة الملف الشخصي وإعدادات الحساب" subtitle="تحديث البيانات الشخصية وكلمة المرور" />
    <x-admin.flash />
    <x-admin.errors />

    <!-- Personal Info Card -->
    <div class="bg-white p-6 md:p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6 text-right">
        <h3 class="font-bold text-base text-gray-900 border-b border-gray-100 pb-3">البيانات الأساسية</h3>

        <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">الاسم الكامل</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="username" class="block text-sm font-semibold text-gray-700 mb-1">اسم المستخدم</label>
                    <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-mono dir-ltr text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                </div>

                <div>
                    <label for="phone" class="block text-sm font-semibold text-gray-700 mb-1">رقم الهاتف</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-mono dir-ltr text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">البريد الإلكتروني (اختياري)</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-[#6F42C1] hover:bg-[#5B2BB8] text-white font-bold rounded-xl shadow-md transition text-xs">
                    تحديث الملف الشخصي
                </button>
            </div>
        </form>
    </div>

    <!-- Password Change Card -->
    <div class="bg-white p-6 md:p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6 text-right">
        <h3 class="font-bold text-base text-gray-900 border-b border-gray-100 pb-3">تغيير كلمة المرور</h3>

        <form method="POST" action="{{ route('admin.profile.change-password') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-1">كلمة المرور الحالية</label>
                <input type="password" id="current_password" name="current_password" required
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">كلمة المرور الجديدة</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">تأكيد كلمة المرور الجديدة</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-purple-900 hover:bg-purple-950 text-white font-bold rounded-xl shadow-md transition text-xs">
                    تغيير كلمة المرور
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
