@extends('layouts.app')

@section('title', 'لوحة تحكم المدير')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="لوحة تحكم المدير" subtitle="نظرة عامة على أداء المنصة" />

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm font-medium text-slate-500">الإيرادات المحصلة · آخر {{ $stats['period_days'] }} يوم</p>
                <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2 text-sm">
                    <label for="period" class="text-slate-500">الفترة</label>
                    <select id="period" name="period" onchange="this.form.submit()" class="rounded-lg border-slate-300 py-1.5 text-sm">
                        <option value="7" @selected($stats['period_days'] === 7)>٧ أيام</option>
                        <option value="30" @selected($stats['period_days'] === 30)>٣٠ يوم</option>
                    </select>
                </form>
            </div>
            <div class="mt-1 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-3xl font-bold tabular-nums text-slate-900">{{ number_format((float) $stats['revenue_period'], 2) }} <span class="text-base font-medium">د.ل</span></p>
                    @php($revenueDelta = (float) $stats['revenue_period'] - (float) $stats['revenue_previous_period'])
                    <p class="mt-1 text-sm {{ $revenueDelta >= 0 ? 'text-emerald-700' : 'text-red-600' }}">{{ $revenueDelta >= 0 ? '↑' : '↓' }} {{ number_format(abs($revenueDelta), 2) }} د.ل مقارنة بالفترة السابقة</p>
                </div>
                <a href="{{ route('staff.payments.index') }}" class="rounded-lg bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100">عرض المدفوعات</a>
            </div>
        </section>
        <section class="rounded-xl border border-amber-200 bg-amber-50 p-5">
            <h2 class="font-semibold text-slate-900">تحتاج متابعة</h2>
            <div class="mt-3 space-y-2 text-sm">
                <a class="flex justify-between gap-3 text-slate-700 hover:text-blue-700" href="{{ route('staff.recharges.index', ['status' => 'pending']) }}"><span>طلبات شحن أقدم من ٢٤ ساعة</span><strong>{{ number_format($stats['stale_recharges']) }}</strong></a>
                <a class="flex justify-between gap-3 text-slate-700 hover:text-blue-700" href="{{ route('staff.complaints.index') }}"><span>شكاوى مفتوحة أقدم من ٤٨ ساعة</span><strong>{{ number_format($stats['stale_complaints']) }}</strong></a>
            </div>
        </section>
    </div>
    <p class="text-xs text-slate-500">المقارنة تغطي الفترة المحددة مقابل الفترة السابقة المماثلة. الإيرادات محسوبة من المدفوعات المكتملة فقط.</p>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2 xl:grid-cols-3">
        @php($subscriberDelta = $stats['new_subscribers_period'] - $stats['new_subscribers_previous_period'])
        @foreach ([
            ['label' => 'إجمالي المشتركين', 'value' => $stats['total_subscribers'], 'hint' => '+' . number_format($stats['new_subscribers_period']) . ' مشترك خلال ' . $stats['period_days'] . ' يوم · ' . ($subscriberDelta >= 0 ? '+' : '') . number_format($subscriberDelta) . ' عن الفترة السابقة', 'color' => 'teal', 'icon' => 'users', 'url' => route('staff.subscribers.index'), 'detail' => 'عرض المشتركين', 'chart' => $stats['subscribers_by_day']],
            ['label' => 'المشتركون النشطون', 'value' => $stats['active_subscribers'], 'hint' => 'حساب مشترك بحالة نشطة', 'color' => 'blue', 'icon' => 'clock', 'url' => route('staff.subscribers.index'), 'detail' => 'عرض المشتركين', 'chart' => $stats['active_subscribers_by_day']],
            ['label' => 'طلبات الشحن المعلّقة', 'value' => $stats['pending_recharges'], 'hint' => 'طلبات بانتظار المراجعة', 'color' => 'orange', 'icon' => 'bag', 'url' => route('staff.recharges.index', ['status' => 'pending']), 'detail' => 'مراجعة الطلبات', 'chart' => $stats['pending_recharges_by_day']],
            ['label' => 'الشكاوى المفتوحة', 'value' => $stats['open_complaints'], 'hint' => number_format($stats['open_complaints']) . ' من ' . number_format($stats['total_complaints']) . ' شكوى', 'color' => 'red', 'icon' => 'pulse', 'url' => route('staff.complaints.index'), 'detail' => 'عرض الشكاوى', 'progress' => $stats['total_complaints'] > 0 ? min(100, round($stats['open_complaints'] / $stats['total_complaints'] * 100)) : 0],
            ['label' => 'إجمالي الموظفين', 'value' => $stats['total_employees'], 'hint' => number_format($stats['active_employees']) . ' حساب نشط', 'color' => 'green', 'icon' => 'users', 'url' => route('admin.employees.index'), 'detail' => 'إدارة الموظفين', 'progress' => $stats['total_employees'] > 0 ? min(100, round($stats['active_employees'] / $stats['total_employees'] * 100)) : 0],
            ['label' => 'الباقات النشطة', 'value' => $stats['active_packages'], 'hint' => number_format($stats['active_packages']) . ' من ' . number_format($stats['total_packages']) . ' باقة', 'color' => 'purple', 'icon' => 'eye', 'url' => route('admin.packages.index'), 'detail' => 'إدارة الباقات', 'progress' => $stats['total_packages'] > 0 ? min(100, round($stats['active_packages'] / $stats['total_packages'] * 100)) : 0],
        ] as $card)
            <a href="{{ $card['url'] }}"
               aria-label="{{ $card['label'] }}: {{ number_format($card['value']) }}. {{ $card['detail'] }}"
               class="group flex min-h-[116px] flex-col justify-between overflow-hidden rounded-xl border border-slate-200 bg-white p-4 shadow-[0_2px_7px_rgba(15,23,42,0.045)] transition duration-200 hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 active:scale-[.995] sm:p-5">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg transition-transform duration-200 group-hover:scale-105
                        {{ $card['color'] === 'teal' ? 'bg-teal-50 text-teal-500' : '' }}
                        {{ $card['color'] === 'blue' ? 'bg-blue-50 text-blue-500' : '' }}
                        {{ $card['color'] === 'orange' ? 'bg-orange-50 text-orange-500' : '' }}
                        {{ $card['color'] === 'red' ? 'bg-red-50 text-red-500' : '' }}
                        {{ $card['color'] === 'green' ? 'bg-green-50 text-green-600' : '' }}
                        {{ $card['color'] === 'purple' ? 'bg-purple-50 text-purple-500' : '' }}" aria-hidden="true">
                        @if ($card['icon'] === 'users')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM20 8v6M23 11h-6"/></svg>
                        @elseif ($card['icon'] === 'clock')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        @elseif ($card['icon'] === 'bag')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M5 8h14l1 13H4L5 8ZM9 8V6a3 3 0 0 1 6 0v2"/><path d="M9 12a3 3 0 0 0 6 0"/></svg>
                        @elseif ($card['icon'] === 'pulse')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h4l3-8 4 16 3-8h4"/></svg>
                        @else
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
                        <p class="mt-0.5 text-2xl font-bold leading-tight tracking-tight text-slate-900 tabular-nums">{{ number_format($card['value']) }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $card['hint'] }}</p>
                    </div>

                    @if (isset($card['chart']))
                        @php($maxChartValue = max($card['chart']) ?: 1)
                        <div class="flex h-9 w-16 shrink-0 items-end justify-end gap-0.5" aria-label="النشاط اليومي خلال آخر سبعة أيام">
                            @foreach ($card['chart'] as $bar)
                                <span class="w-1 rounded-full transition-opacity duration-200 group-hover:opacity-70
                                    {{ $card['color'] === 'teal' ? 'bg-teal-300' : '' }}
                                    {{ $card['color'] === 'blue' ? 'bg-blue-300' : '' }}
                                    {{ $card['color'] === 'orange' ? 'bg-orange-300' : '' }}"
                                    style="height: {{ max(12, (int) round($bar / $maxChartValue * 100)) }}%"></span>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if (isset($card['progress']))
                    <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-label="{{ $card['hint'] }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $card['progress'] }}">
                        <div class="h-full rounded-full transition-all duration-300 group-hover:brightness-90
                            {{ $card['color'] === 'red' ? 'bg-red-500' : '' }}
                            {{ $card['color'] === 'green' ? 'bg-green-500' : '' }}
                            {{ $card['color'] === 'purple' ? 'bg-purple-500' : '' }}"
                            style="width: {{ $card['progress'] }}%"></div>
                    </div>
                @endif
            </a>
        @endforeach
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @can('view_audit_logs')
        <a href="{{ route('admin.audit-logs.index') }}" class="bg-white rounded-2xl border border-gray-100 p-5 hover:border-purple-200 transition shadow-sm">
            <h3 class="font-bold text-gray-900">سجل العمليات</h3>
            <p class="text-xs text-gray-400 mt-1">مراجعة العمليات الإدارية</p>
        </a>
        @endcan
    </div>
</div>
@endsection
