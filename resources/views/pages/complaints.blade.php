@extends('layouts.app')

@section('title', 'إدارة الشكاوى والدعم الفني')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
        <div>
            <h2 class="text-xl font-black text-gray-900">إدارة الشكاوى والطلبات</h2>
            <p class="text-xs text-gray-400 mt-1">متابعة وحل شكاوى المشتركين والدعم الفني الميداني</p>
        </div>
        <button id="openComplaintModal" type="button" class="px-4 py-2.5 bg-[#6F42C1] hover:bg-[#5B2BB8] text-white font-bold rounded-xl text-xs shadow-md transition cursor-pointer">
            + إنشاء تذكرة دعم
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

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 font-bold">
                        <th class="py-4 px-6">رقم التذكرة</th>
                        <th class="py-4 px-6">المشترك</th>
                        <th class="py-4 px-6">نوع الشكوى / الموضوع</th>
                        <th class="py-4 px-6">التاريخ</th>
                        <th class="py-4 px-6">الحالة</th>
                        <th class="py-4 px-6">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse ($dbComplaints as $c)
                        <tr class="hover:bg-purple-50/20 transition">
                            <td class="py-4 px-6 font-mono text-purple-700 font-bold">#TK-{{ $c->id }}</td>
                            <td class="py-4 px-6 font-bold text-gray-900">{{ $c->user?->name ?? 'مشترك عام' }}</td>
                            <td class="py-4 px-6 font-medium text-gray-700">{{ $c->subject }}</td>
                            <td class="py-4 px-6 text-gray-400 font-mono text-[11px]">{{ $c->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="py-4 px-6">
                                @if ($c->status === 'resolved' || $c->status === 'closed')
                                    <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-semibold">تم الحل</span>
                                @elseif ($c->status === 'in_progress')
                                    <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 font-semibold">قيد المعالجة</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 font-semibold">مفتوحة</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-purple-600 font-bold text-xs">تذكرة مسجلة</span>
                            </td>
                        </tr>
                    @empty
                    @endforelse

                    @foreach($complaints as $c)
                    <tr class="hover:bg-purple-50/20 transition">
                        <td class="py-4 px-6 font-mono text-purple-700 font-bold">{{ $c['id'] }}</td>
                        <td class="py-4 px-6 font-bold text-gray-900">{{ $c['subscriber'] }}</td>
                        <td class="py-4 px-6 font-medium text-gray-700">{{ $c['type'] }}</td>
                        <td class="py-4 px-6 text-gray-400 font-mono text-[11px]">{{ $c['date'] }}</td>
                        <td class="py-4 px-6">
                            <x-status-badge :status="$c['status'] == 'تم الحل' ? 'completed' : ($c['status'] == 'قيد المعالجة' ? 'pending' : 'failed')" :label="$c['status']" />
                        </td>
                        <td class="py-4 px-6">
                            <span class="text-gray-400 font-bold text-xs">معالجة التذكرة</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Complaint Modal -->
<div id="complaintModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 md:p-8 space-y-6 shadow-2xl relative text-right">
        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
            <h3 class="text-lg font-bold text-gray-900">إنشاء تذكرة دعم / إضافة شكوى</h3>
            <button id="closeComplaintModal" type="button" class="text-gray-400 hover:text-gray-600 text-xl font-bold p-1">×</button>
        </div>

        <form method="POST" action="{{ route('staff.complaints.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="user_id" class="block text-xs font-semibold text-gray-700 mb-1">المشترك المعني (اختياري)</label>
                <select id="user_id" name="user_id"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                    <option value="">-- اختر المشترك --</option>
                    @foreach ($subscribers as $sub)
                        <option value="{{ $sub->id }}" @selected(old('user_id') == $sub->id)>{{ $sub->name }} ({{ $sub->username }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="subject" class="block text-xs font-semibold text-gray-700 mb-1">موضوع الشكوى / المشكلة</label>
                <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                       placeholder="مثال: بطء شديد في السرعة المسائية / انقطاع الإشارة">
            </div>

            <div>
                <label for="comp_status" class="block text-xs font-semibold text-gray-700 mb-1">حالة التذكرة</label>
                <select id="comp_status" name="status" required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                    <option value="open" @selected(old('status') === 'open')>مفتوحة (Open)</option>
                    <option value="in_progress" @selected(old('status') === 'in_progress')>قيد المعالجة (In Progress)</option>
                    <option value="resolved" @selected(old('status') === 'resolved')>تم الحل (Resolved)</option>
                    <option value="closed" @selected(old('status') === 'closed')>مغلقة (Closed)</option>
                </select>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
                <button id="cancelComplaintModal" type="button" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-xs transition">
                    إلغاء
                </button>
                <button type="submit" class="px-6 py-2.5 bg-[#6F42C1] hover:bg-[#5B2BB8] text-white font-bold rounded-xl text-xs shadow-md transition">
                    حفظ تذكرة الدعم
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('complaintModal');
        const openBtn = document.getElementById('openComplaintModal');
        const closeBtn = document.getElementById('closeComplaintModal');
        const cancelBtn = document.getElementById('cancelComplaintModal');

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
