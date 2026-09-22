@extends('layouts.app')

@section('title', 'إدارة المشتركين')

@section('content')
<div class="space-y-6">
    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
        <div>
            <h2 class="text-xl font-black text-gray-900">إدارة المشتركين</h2>
            <p class="text-xs text-gray-400 mt-1">عرض وإدارة بيانات المشتركين واشتراكاتهم</p>
        </div>
        <button id="openSubscriberModal" type="button" class="px-4 py-2.5 bg-[#6F42C1] hover:bg-[#5B2BB8] text-white font-bold rounded-xl text-xs shadow-md transition flex items-center gap-2 self-start sm:self-auto cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>إضافة مشترك جديد</span>
        </button>
    </div>

    @if (session('status'))
        <div class="rounded-2xl bg-emerald-50 border border-emerald-100 px-5 py-4 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-2xl bg-red-50 border border-red-100 px-5 py-4 text-sm text-red-800 space-y-1">
            @foreach ($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- Table Container -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 font-bold">
                        <th class="py-4 px-6">المشترك</th>
                        <th class="py-4 px-6">اسم المستخدم</th>
                        <th class="py-4 px-6">رقم الهاتف</th>
                        <th class="py-4 px-6">الحالة</th>
                        <th class="py-4 px-6">تاريخ التسجيل</th>
                        <th class="py-4 px-6">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse ($dbSubscribers as $sub)
                        <tr class="hover:bg-purple-50/20 transition">
                            <td class="py-4 px-6 font-bold text-gray-900">{{ $sub->name }}</td>
                            <td class="py-4 px-6 font-mono text-purple-700 font-bold dir-ltr text-right">{{ $sub->username }}</td>
                            <td class="py-4 px-6 font-mono dir-ltr text-right">{{ $sub->phone ?? '—' }}</td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-semibold">نشط</span>
                            </td>
                            <td class="py-4 px-6 font-mono text-gray-400 text-[11px]">{{ $sub->created_at?->format('Y-m-d') }}</td>
                            <td class="py-4 px-6">
                                <span class="text-purple-600 font-bold text-xs">حساب مشترك</span>
                            </td>
                        </tr>
                    @empty
                    @endforelse

                    @foreach($subscribers as $sub)
                        <tr class="hover:bg-purple-50/20 transition">
                            <td class="py-4 px-6 font-bold text-gray-900">{{ $sub['name'] }}</td>
                            <td class="py-4 px-6 font-mono text-purple-700 font-bold">#{{ $sub['contract'] }}</td>
                            <td class="py-4 px-6 font-mono dir-ltr text-right">{{ $sub['phone'] }}</td>
                            <td class="py-4 px-6">
                                <x-status-badge :status="$sub['status'] == 'نشط' ? 'active' : ($sub['status'] == 'منتهي' ? 'expired' : 'suspended')" :label="$sub['status']" />
                            </td>
                            <td class="py-4 px-6 font-mono text-gray-400 text-[11px]">{{ $sub['end_date'] }}</td>
                            <td class="py-4 px-6">
                                <span class="text-gray-400 text-xs">عرض الملف</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Subscriber Modal -->
<div id="subscriberModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 md:p-8 space-y-6 shadow-2xl relative text-right">
        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
            <h3 class="text-lg font-bold text-gray-900">إضافة مشترك جديد</h3>
            <button id="closeSubscriberModal" type="button" class="text-gray-400 hover:text-gray-600 text-xl font-bold p-1">×</button>
        </div>

        <form method="POST" action="{{ route('staff.subscribers.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="sub_name" class="block text-xs font-semibold text-gray-700 mb-1">الاسم الكامل</label>
                <input type="text" id="sub_name" name="name" value="{{ old('name') }}" required
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                       placeholder="مثال: محمد سالم علي">
            </div>

            <div>
                <label for="sub_username" class="block text-xs font-semibold text-gray-700 mb-1">اسم المستخدم (للوصول)</label>
                <input type="text" id="sub_username" name="username" value="{{ old('username') }}" required
                       autocomplete="off"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-mono dir-ltr text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                       placeholder="mohamed_ali">
            </div>

            <div>
                <label for="sub_phone" class="block text-xs font-semibold text-gray-700 mb-1">رقم الهاتف</label>
                <input type="text" id="sub_phone" name="phone" value="{{ old('phone') }}" required
                       autocomplete="off"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-mono dir-ltr text-right focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                       placeholder="0910000000">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="sub_password" class="block text-xs font-semibold text-gray-700 mb-1">كلمة المرور</label>
                    <input type="password" id="sub_password" name="password" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                </div>
                <div>
                    <label for="sub_password_confirmation" class="block text-xs font-semibold text-gray-700 mb-1">تأكيد كلمة المرور</label>
                    <input type="password" id="sub_password_confirmation" name="password_confirmation" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
                <button id="cancelSubscriberModal" type="button" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-xs transition">
                    إلغاء
                </button>
                <button type="submit" class="px-6 py-2.5 bg-[#6F42C1] hover:bg-[#5B2BB8] text-white font-bold rounded-xl text-xs shadow-md transition">
                    إنشاء حساب المشترك
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('subscriberModal');
        const openBtn = document.getElementById('openSubscriberModal');
        const closeBtn = document.getElementById('closeSubscriberModal');
        const cancelBtn = document.getElementById('cancelSubscriberModal');

        const openModal = () => modal.classList.remove('hidden');
        const closeModal = () => modal.classList.add('hidden');

        if (openBtn) openBtn.addEventListener('click', openModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

        @if ($errors->any())
            openModal();
        @endif
    });
</script>
@endsection
