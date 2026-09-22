@props(['status', 'label' => null])

@php
    $statusMap = [
        'completed' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60', 'icon' => 'check-circle', 'default_label' => 'تم بنجاح'],
        'pending' => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200/60', 'icon' => 'clock', 'default_label' => 'قيد المعالجة'],
        'failed' => ['bg' => 'bg-rose-50 text-rose-700 border-rose-200/60', 'icon' => 'x-circle', 'default_label' => 'فشلت'],
        'active' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60', 'icon' => 'check-circle', 'default_label' => 'نشط'],
        'expired' => ['bg' => 'bg-gray-100 text-gray-600 border-gray-200', 'icon' => 'alert-circle', 'default_label' => 'منتهي'],
        'suspended' => ['bg' => 'bg-rose-50 text-rose-700 border-rose-200/60', 'icon' => 'x-circle', 'default_label' => 'موقوف'],
    ];

    $config = $statusMap[$status] ?? ['bg' => 'bg-purple-50 text-purple-700 border-purple-200', 'icon' => 'info', 'default_label' => $status];
    $displayLabel = $label ?? $config['default_label'];
@endphp

<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $config['bg'] }}">
    @if($config['icon'] === 'check-circle')
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    @elseif($config['icon'] === 'clock')
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    @elseif($config['icon'] === 'x-circle')
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    @endif
    <span>{{ $displayLabel }}</span>
</span>
