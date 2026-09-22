@props(['title', 'subtitle' => null, 'actionUrl' => null, 'actionLabel' => null])

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
    <div>
        <h2 class="text-xl font-black text-gray-900">{{ $title }}</h2>
        @if ($subtitle)
            <p class="text-xs text-gray-400 mt-1">{{ $subtitle }}</p>
        @endif
    </div>
    @if ($actionUrl && $actionLabel)
        <a href="{{ $actionUrl }}"
           class="px-4 py-2.5 bg-[#6F42C1] hover:bg-[#5B2BB8] text-white font-bold rounded-xl text-xs shadow-md transition flex items-center gap-2 self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>{{ $actionLabel }}</span>
        </a>
    @endif
</div>
