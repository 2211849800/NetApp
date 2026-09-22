@props(['backUrl' => null, 'backLabel' => '← العودة'])

<div class="bg-white p-6 md:p-8 rounded-3xl border border-gray-100 shadow-sm">
    @if ($backUrl)
        <div class="mb-6">
            <a href="{{ $backUrl }}" class="text-xs font-semibold text-purple-600 hover:text-purple-800">{{ $backLabel }}</a>
        </div>
    @endif
    <x-admin.errors />
    {{ $slot }}
</div>
