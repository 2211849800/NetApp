@extends('layouts.app')

@section('title', 'إدارة الموظفين')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
        <div>
            <h2 class="text-xl font-black text-gray-900">إدارة الموظفين</h2>
            <p class="text-xs text-gray-400 mt-1">إنشاء حسابات الموظفين والمشرفين وتسليم بيانات الدخول</p>
        </div>
        <a href="{{ route('admin.employees.create') }}"
           class="px-4 py-2.5 bg-[#6F42C1] hover:bg-[#5B2BB8] text-white font-bold rounded-xl text-xs shadow-md transition flex items-center gap-2 self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>إضافة موظف</span>
        </a>
    </div>

    @if (session('status'))
        <div class="rounded-2xl bg-emerald-50 border border-emerald-100 px-5 py-4 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 font-bold">
                        <th class="py-4 px-6">الاسم</th>
                        <th class="py-4 px-6">اسم المستخدم</th>
                        <th class="py-4 px-6">رقم الهاتف</th>
                        <th class="py-4 px-6">الدور</th>
                        <th class="py-4 px-6">الحالة</th>
                        <th class="py-4 px-6">تاريخ الإنشاء</th>
                        <th class="py-4 px-6">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse ($employees as $employee)
                        <tr class="hover:bg-purple-50/20 transition">
                            <td class="py-4 px-6 font-bold text-gray-900">
                                <a href="{{ route('admin.employees.show', $employee) }}" class="hover:text-purple-700">
                                    {{ $employee->name }}
                                </a>
                            </td>
                            <td class="py-4 px-6 font-mono text-purple-700 font-bold dir-ltr text-right">{{ $employee->username }}</td>
                            <td class="py-4 px-6 font-mono text-gray-600 dir-ltr text-right">{{ $employee->phone ?? '—' }}</td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 font-semibold">
                                    {{ $employee->roles->first()?->display_name ?? '—' }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                @if ($employee->status?->value === 'active')
                                    <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-semibold">نشط</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 font-semibold">{{ $employee->status?->label() }}</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-gray-500">{{ $employee->created_at?->format('Y-m-d') }}</td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.employees.show', $employee) }}" class="p-1.5 text-gray-400 hover:text-purple-700 hover:bg-purple-50 rounded-lg transition" title="عرض">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.employees.edit', $employee) }}" class="p-1.5 text-gray-400 hover:text-purple-700 hover:bg-purple-50 rounded-lg transition" title="تعديل">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.employees.reset-password-form', $employee) }}" class="p-1.5 text-gray-400 hover:text-amber-700 hover:bg-amber-50 rounded-lg transition" title="تغيير كلمة المرور">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 px-6 text-center text-gray-400">لا يوجد موظفون بعد. ابدأ بإضافة موظف جديد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
