@extends('layouts.app')

@section('title', 'إعادة تعيين كلمة المرور')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <x-admin.page-header title="إعادة تعيين كلمة المرور للموظف" subtitle="{{ $employee->name }} ({{ $employee->username }})" />
    <x-admin.errors />

    <div class="bg-white p-6 md:p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
        <div class="rounded-2xl bg-amber-50 border border-amber-100 p-4 text-xs text-amber-800 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <p>سيتم تعديل كلمة مرور الموظف مباشرة. يرجى تسليم كلمة المرور الجديدة للموظف بأمان.</p>
        </div>

        <form method="POST" action="{{ route('admin.employees.reset-password', $employee) }}" class="space-y-4">
            @csrf

            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">كلمة المرور الجديدة</label>
                <input type="password" id="password" name="password" required
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1.5">تأكيد كلمة المرور الجديدة</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('admin.employees.show', $employee) }}" class="text-xs font-semibold text-gray-500 hover:text-purple-700">
                    ← إلغاء والعودة
                </a>
                <button type="submit" class="px-6 py-3 bg-purple-700 hover:bg-purple-800 text-white font-bold rounded-xl shadow-md transition text-xs">
                    تعيين كلمة المرور الجديدة
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
