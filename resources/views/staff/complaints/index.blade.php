@extends('layouts.app')

@section('title', 'إدارة الشكاوى والدعم الفني')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
        <div>
            <h2 class="text-xl font-black text-gray-900">إدارة الشكاوى والطلبات</h2>
            <p class="text-xs text-gray-400 mt-1">جميع التذاكر مربوطة بجدول الشكاوى — بدون بيانات وهمية</p>
        </div>
        <button id="openComplaintModal" type="button" class="px-4 py-2.5 bg-[#6F42C1] hover:bg-[#5B2BB8] text-white font-bold rounded-xl text-xs shadow-md cursor-pointer">
            + إنشاء تذكرة دعم
        </button>
    </div>

    <x-admin.flash />
    <x-admin.errors />

    <form method="GET" class="bg-white p-4 rounded-3xl border border-gray-100 flex flex-col sm:flex-row gap-3">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="بحث بالموضوع أو الاسم أو رقم العقد"
               class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-xs">
        <select name="status" class="px-4 py-2.5 rounded-xl border border-gray-200 text-xs">
            <option value="">كل الحالات</option>
            @foreach (['open' => 'مفتوحة', 'in_progress' => 'قيد المعالجة', 'resolved' => 'تم الحل', 'closed' => 'مغلقة'] as $value => $label)
                <option value="{{ $value }}" @selected(($status ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="px-5 py-2.5 bg-[#6F42C1] text-white font-bold rounded-xl text-xs">تصفية</button>
    </form>

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b text-gray-500 font-bold">
                        <th class="py-4 px-6">رقم التذكرة</th>
                        <th class="py-4 px-6">المشترك</th>
                        <th class="py-4 px-6">الموضوع</th>
                        <th class="py-4 px-6">الأولوية</th>
                        <th class="py-4 px-6">التاريخ</th>
                        <th class="py-4 px-6">الحالة</th>
                        <th class="py-4 px-6">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($complaints as $c)
                        <tr class="hover:bg-purple-50/20">
                            <td class="py-4 px-6 font-mono text-purple-700 font-bold">#TK-{{ $c->id }}</td>
                            <td class="py-4 px-6">
                                <p class="font-bold">{{ $c->subscriber_name ?: ($c->user?->name ?? 'مشترك عام') }}</p>
                                <p class="font-mono text-gray-400">{{ $c->contract_number ?: '—' }}</p>
                            </td>
                            <td class="py-4 px-6">{{ $c->subject }}</td>
                            <td class="py-4 px-6">{{ $c->priorityLabel() }}</td>
                            <td class="py-4 px-6 font-mono text-gray-400">{{ $c->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="py-4 px-6">
                                @php
                                    $badge = match ($c->status) {
                                        'resolved', 'closed' => 'completed',
                                        'in_progress' => 'pending',
                                        default => 'failed',
                                    };
                                @endphp
                                <x-status-badge :status="$badge" :label="$c->statusLabel()" />
                            </td>
                            <td class="py-4 px-6">
                                <a href="{{ route('staff.complaints.show', $c) }}" class="text-purple-700 font-bold">معالجة</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-10 text-center text-gray-400">لا توجد شكاوى مسجّلة.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $complaints->links() }}</div>
    </div>
</div>

<div id="complaintModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 md:p-8 space-y-5 shadow-2xl">
        <div class="flex items-center justify-between border-b pb-4">
            <h3 class="text-lg font-bold">إنشاء تذكرة دعم</h3>
            <button id="closeComplaintModal" type="button" class="text-xl text-gray-400">×</button>
        </div>
        <form method="POST" action="{{ route('staff.complaints.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold mb-1">المشترك (اختياري)</label>
                <select name="user_id" class="w-full px-4 py-2.5 rounded-xl border text-xs">
                    <option value="">-- اختر --</option>
                    @foreach ($subscribers as $sub)
                        <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->contract_number ?: $sub->username }})</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold mb-1">رقم العقد</label>
                    <input name="contract_number" value="{{ old('contract_number') }}" class="w-full px-3 py-2 rounded-xl border text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">اسم المشترك</label>
                    <input name="subscriber_name" value="{{ old('subscriber_name') }}" class="w-full px-3 py-2 rounded-xl border text-xs">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">الموضوع</label>
                <input name="subject" value="{{ old('subject') }}" required class="w-full px-3 py-2 rounded-xl border text-xs">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">الوصف</label>
                <textarea name="description" rows="3" class="w-full px-3 py-2 rounded-xl border text-xs">{{ old('description') }}</textarea>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold mb-1">النوع</label>
                    <input name="type" value="{{ old('type') }}" class="w-full px-3 py-2 rounded-xl border text-xs" placeholder="انقطاع / بطء">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">الأولوية</label>
                    <select name="priority" class="w-full px-3 py-2 rounded-xl border text-xs">
                        <option value="medium">متوسطة</option>
                        <option value="high">عالية</option>
                        <option value="low">منخفضة</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">الحالة</label>
                    <select name="status" class="w-full px-3 py-2 rounded-xl border text-xs">
                        <option value="open">مفتوحة</option>
                        <option value="in_progress">قيد المعالجة</option>
                        <option value="resolved">تم الحل</option>
                        <option value="closed">مغلقة</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-3">
                <button id="cancelComplaintModal" type="button" class="px-4 py-2 bg-gray-100 rounded-xl text-xs font-semibold">إلغاء</button>
                <button class="px-5 py-2 bg-[#6F42C1] text-white rounded-xl text-xs font-bold">حفظ التذكرة</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('complaintModal');
        const open = () => modal.classList.remove('hidden');
        const close = () => modal.classList.add('hidden');
        document.getElementById('openComplaintModal')?.addEventListener('click', open);
        document.getElementById('closeComplaintModal')?.addEventListener('click', close);
        document.getElementById('cancelComplaintModal')?.addEventListener('click', close);
        @if ($errors->any()) open(); @endif
    });
</script>
@endpush
