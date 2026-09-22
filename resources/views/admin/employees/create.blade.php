@extends('layouts.app')

@section('title', 'إضافة موظف')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-gray-900">إضافة موظف جديد</h2>
                <p class="text-xs text-gray-400 mt-1">أنشئ اسم مستخدم وكلمة مرور للموظف</p>
            </div>
            <a href="{{ route('admin.employees.index') }}" class="text-xs font-semibold text-purple-600 hover:text-purple-800">
                ← العودة للقائمة
            </a>
        </div>
    </div>

    <div class="bg-white p-6 md:p-8 rounded-3xl border border-gray-100 shadow-sm">
        @if ($errors->any())
            <div class="mb-6 rounded-2xl bg-red-50 border border-red-100 px-4 py-3 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.employees.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5">الاسم الكامل</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
            </div>

            <div>
                <label for="username" class="block text-sm font-semibold text-gray-700 mb-1.5">اسم المستخدم</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required
                       autocomplete="off"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-mono dir-ltr text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                       placeholder="ahmed_ali">
                <p class="text-[11px] text-gray-400 mt-1">يُستخدم لتسجيل الدخول — حروف إنجليزية وأرقام و _ فقط</p>
            </div>

            <div>
                <label for="phone" class="block text-sm font-semibold text-gray-700 mb-1.5">رقم الهاتف</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required
                       autocomplete="off"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-mono dir-ltr text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                       placeholder="0910000000">
            </div>

            <div>
                <label for="role" class="block text-sm font-semibold text-gray-700 mb-1.5">الدور</label>
                <select id="role" name="role" required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                    <option value="employee" @selected(old('role') === 'employee')>موظف (Employee)</option>
                    <option value="supervisor" @selected(old('role') === 'supervisor')>مشرف (Supervisor)</option>
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">كلمة المرور</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1.5">تأكيد كلمة المرور</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                </div>
            </div>

            <button type="submit"
                    class="w-full py-3 bg-[#6F42C1] hover:bg-[#5B2BB8] text-white font-bold rounded-xl shadow-md transition">
                إنشاء حساب الموظف
            </button>
        </form>
    </div>
</div>
@endsection
