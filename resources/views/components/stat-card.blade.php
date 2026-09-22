@props([
    'title',
    'value',
    'change',
    'period' => 'منذ الشهر الماضي',
    'trend' => 'up',
    'icon' => 'users'
])

<div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200">
    <div class="flex items-start justify-between">
        <div class="space-y-1">
            <span class="text-xs font-medium text-gray-400 block">{{ $title }}</span>
            <div class="text-2xl font-black text-gray-900 tracking-tight leading-none pt-1">{{ $value }}</div>
        </div>
        
        <div class="w-11 h-11 rounded-2xl flex items-center justify-center 
            {{ $icon === 'message-square' ? 'bg-amber-50 text-amber-600' : ($icon === 'user-check' ? 'bg-emerald-50 text-emerald-600' : 'bg-purple-50 text-purple-600') }}">
            @if($icon === 'users')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            @elseif($icon === 'user-check')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            @elseif($icon === 'wallet')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            @else
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            @endif
        </div>
    </div>

    <!-- Trend line at bottom -->
    <div class="mt-4 flex items-center gap-1.5 text-xs font-semibold {{ $trend === 'up' ? 'text-emerald-600' : 'text-rose-500' }}">
        <span>{{ $change }}</span>
        <span class="text-[11px] font-normal text-gray-400">{{ $period }}</span>
    </div>
</div>
