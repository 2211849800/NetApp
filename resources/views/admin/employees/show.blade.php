@extends('layouts.app')

@section('title', 'تفاصيل الموظف')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <x-admin.page-header title="تفاصيل حساب الموظف: {{ $employee->name }}" :action-url="route('admin.employees.edit', $employee)" action-label="تعديل البيانات" />
    <x-admin.flash />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Basic Info -->
        <div class="md:col-span-1 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4 text-right">
            <div class="flex flex-col items-center text-center pb-4 border-b border-gray-100">
                <div class="w-16 h-16 rounded-2xl bg-purple-100 text-purple-700 font-black text-2xl flex items-center justify-center mb-3">
                    {{ mb_substr($employee->name, 0, 1) }}
                </div>
                <h3 class="font-bold text-base text-gray-900">{{ $employee->name }}</h3>
                <span class="mt-1 px-3 py-1 rounded-lg bg-purple-50 text-purple-700 text-xs font-semibold">
                    {{ $employee->roles->first()?->display_name ?? 'موظف' }}
                </span>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <p class="text-gray-400 font-semibold">اسم المستخدم</p>
                    <p class="font-mono text-purple-700 font-bold dir-ltr text-right mt-0.5">{{ $employee->username }}</p>
                </div>
                <div>
                    <p class="text-gray-400 font-semibold">رقم الهاتف</p>
                    <p class="font-mono text-gray-800 font-bold dir-ltr text-right mt-0.5">{{ $employee->phone ?? 'غير مدخل' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 font-semibold">البريد الإلكتروني</p>
                    <p class="font-semibold text-gray-700 mt-0.5">{{ $employee->email ?? 'غير مدخل' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 font-semibold">حالة الحساب</p>
                    <p class="mt-0.5">
                        @if ($employee->status?->value === 'active')
                            <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-bold">نشط (Active)</span>
                        @else
                            <span class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 font-bold">{{ $employee->status?->label() }}</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-gray-400 font-semibold">تاريخ الانضمام</p>
                    <p class="font-semibold text-gray-700 mt-0.5">{{ $employee->created_at?->format('Y-m-d H:i') }}</p>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 space-y-2">
                <a href="{{ route('admin.employees.reset-password-form', $employee) }}"
                   class="block w-full py-2.5 text-center bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-xs rounded-xl transition">
                    إعادة تعيين كلمة المرور
                </a>

                @if ($employee->status?->value !== 'active')
                    <form method="POST" action="{{ route('admin.employees.activate', $employee) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs rounded-xl transition">
                            تفعيل حساب الموظف
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.employees.deactivate', $employee) }}" onsubmit="return confirm('هل أنت تأكد من تعطيل حساب هذا الموظف؟')">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="confirm" value="yes">
                        <button type="submit" class="w-full py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded-xl transition">
                            تعطيل حساب الموظف
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Employee Activity Logs -->
        <div class="md:col-span-2 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4">
            <h4 class="font-bold text-sm text-gray-900 border-b border-gray-100 pb-3">آخر العمليات والنشاطات لهذا الموظف</h4>

            <div class="space-y-3">
                @forelse ($activity as $log)
                    <div class="p-3.5 bg-gray-50/70 rounded-2xl border border-gray-100 flex items-center justify-between text-xs">
                        <div>
                            <p class="font-bold text-purple-900">{{ $log->action }}</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">IP: {{ $log->ip_address ?? '—' }}</p>
                        </div>
                        <div class="text-left text-gray-400">
                            {{ $log->created_at?->diffForHumans() }}
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-gray-400">
                        لا تتوفر أي سجلات نشاط سابقة لهذا الموظف.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
