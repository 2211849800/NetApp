@props(['item', 'editRoute', 'toggleRoute', 'showRoute' => null])

<div class="flex items-center gap-2 flex-wrap">
    @if ($showRoute)
        <a href="{{ $showRoute }}" class="text-purple-600 hover:underline font-semibold">عرض</a>
    @endif
    <a href="{{ $editRoute }}" class="text-blue-600 hover:underline font-semibold">تعديل</a>
    <form method="POST" action="{{ $toggleRoute }}" class="inline" onsubmit="return confirm('هل أنت متأكد من تغيير الحالة؟')">
        @csrf
        @method('PATCH')
        <button type="submit" class="text-amber-600 hover:underline font-semibold">
            {{ $item->status?->value === 'active' ? 'تعطيل' : 'تفعيل' }}
        </button>
    </form>
</div>
