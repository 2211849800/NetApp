@extends('layouts.app')

@section('title', 'معالجة شكوى')

@section('content')
<div class="space-y-6 max-w-3xl">
    <x-admin.page-header title="تذكرة #TK-{{ $complaint->id }}" :subtitle="$complaint->subject" />
    <x-admin.flash />
    <x-admin.errors />

    <div class="bg-white rounded-3xl border p-6 space-y-3 text-sm">
        <p><span class="text-gray-400 text-xs">المشترك:</span> <strong>{{ $complaint->subscriber_name ?: ($complaint->user?->name ?? '—') }}</strong></p>
        <p><span class="text-gray-400 text-xs">العقد:</span> <span class="font-mono">{{ $complaint->contract_number ?: '—' }}</span></p>
        <p><span class="text-gray-400 text-xs">النوع / الأولوية:</span> {{ $complaint->type ?: '—' }} · {{ $complaint->priorityLabel() }}</p>
        <p class="text-gray-700 whitespace-pre-line">{{ $complaint->description ?: 'لا يوجد وصف.' }}</p>
        <p class="text-xs text-gray-400">أُنشئت {{ $complaint->created_at?->format('Y-m-d H:i') }}</p>
    </div>

    @can('manage_complaints')
    <form method="POST" action="{{ route('staff.complaints.update', $complaint) }}" class="bg-white rounded-3xl border p-6 space-y-4">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold mb-1">الحالة</label>
                <select name="status" class="w-full px-3 py-2 rounded-xl border text-xs">
                    @foreach (['open' => 'مفتوحة', 'in_progress' => 'قيد المعالجة', 'resolved' => 'تم الحل', 'closed' => 'مغلقة'] as $value => $label)
                        <option value="{{ $value }}" @selected($complaint->status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">إسناد إلى</label>
                <select name="assigned_to" class="w-full px-3 py-2 rounded-xl border text-xs">
                    <option value="">—</option>
                    @foreach ($staff as $member)
                        <option value="{{ $member->id }}" @selected($complaint->assigned_to == $member->id)>{{ $member->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">الأولوية</label>
                <select name="priority" class="w-full px-3 py-2 rounded-xl border text-xs">
                    <option value="low" @selected($complaint->priority === 'low')>منخفضة</option>
                    <option value="medium" @selected($complaint->priority === 'medium')>متوسطة</option>
                    <option value="high" @selected($complaint->priority === 'high')>عالية</option>
                </select>
            </div>
        </div>
        <div>
            <label class="block text-xs font-semibold mb-1">ملاحظات داخلية</label>
            <textarea name="internal_notes" rows="3" class="w-full rounded-xl border text-xs px-3 py-2">{{ old('internal_notes', $complaint->internal_notes) }}</textarea>
        </div>
        <div>
            <label class="block text-xs font-semibold mb-1">الرد للمشترك</label>
            <textarea name="staff_reply" rows="3" class="w-full rounded-xl border text-xs px-3 py-2">{{ old('staff_reply', $complaint->staff_reply) }}</textarea>
        </div>
        <button class="px-6 py-2.5 bg-[#6F42C1] text-white font-bold rounded-xl text-xs">حفظ التحديث</button>
    </form>
    @endcan

    <a href="{{ route('staff.complaints.index') }}" class="text-xs font-bold text-purple-700">العودة للقائمة</a>
</div>
@endsection
