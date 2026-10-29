@extends('layouts.app')

@section('title', 'إدارة المشتركين')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
        <div>
            <h2 class="text-xl font-black text-gray-900">إدارة المشتركين</h2>
            <p class="text-xs text-gray-400 mt-1">استعلام سريع برقم العقد أو الاسم أو الهاتف — البيانات من مزود المشتركين مباشرة</p>
        </div>
        <a href="{{ route('staff.subscribers.create') }}" class="px-4 py-2.5 bg-[#6F42C1] hover:bg-[#5B2BB8] text-white font-bold rounded-xl text-xs shadow-md transition flex items-center gap-2 self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>إضافة عقد جديد</span>
        </a>
    </div>

    <x-admin.flash />
    <x-admin.errors />

    <form method="GET" action="{{ route('staff.subscribers.index') }}" class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm">
        <label class="block text-xs font-semibold text-gray-700 mb-2">بحث سريع</label>
        <div class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="q" value="{{ $query }}"
                   class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                   placeholder="مثال: TEST-100001 أو خالد أو 0912345678">
            <button type="submit" class="px-6 py-2.5 bg-[#6F42C1] hover:bg-[#5B2BB8] text-white font-bold rounded-xl text-xs shadow-md">بحث</button>
        </div>
        <p class="text-[11px] text-gray-400 mt-2">أرقام العقود التجريبية: TEST-100001 إلى TEST-100025</p>
    </form>

    @if ($selected)
        @php
            $sub = $selected['subscriber'];
            $subscription = $selected['subscription'];
            $usage = $selected['usage'];
            $borrowing = $selected['borrowing'];
            $statusKey = match ($sub->status) {
                'ACTIVE' => 'active',
                'EXPIRED' => 'expired',
                'SUSPENDED' => 'suspended',
                default => 'pending',
            };
            $statusLabel = match ($sub->status) {
                'ACTIVE' => 'نشط',
                'EXPIRED' => 'منتهي',
                'SUSPENDED' => 'موقوف',
                default => $sub->status,
            };
            $borrowLabel = match ($borrowing->status) {
                'ACTIVE' => 'سلفة مفعّلة',
                'AVAILABLE' => 'مؤهل للسلفة',
                'EXPIRED' => 'انتهت السلفة',
                default => 'غير مؤهل',
            };
        @endphp

        <div class="bg-white rounded-3xl border border-purple-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-l from-purple-50 to-white border-b border-purple-100 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-black text-gray-900">{{ $sub->name }}</h3>
                    <p class="text-xs font-mono text-purple-700 mt-1 dir-ltr text-right">{{ $sub->contractNumber }} · {{ $sub->phone ?: '—' }}</p>
                </div>
                <x-status-badge :status="$statusKey" :label="$statusLabel" />
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 p-6">
                <div class="rounded-2xl bg-gray-50 p-4">
                    <p class="text-[11px] text-gray-400">الباقة الحالية</p>
                    <p class="font-bold text-gray-900 mt-1">{{ $subscription->packageName }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ number_format($subscription->packagePrice, 2) }} د.ل</p>
                </div>
                <div class="rounded-2xl bg-gray-50 p-4">
                    <p class="text-[11px] text-gray-400">الاستهلاك الحالي</p>
                    <p class="font-bold text-gray-900 mt-1">{{ number_format($usage->usedDataGb, 2) }} GB</p>
                    <p class="text-xs text-gray-500 mt-0.5">من {{ number_format($usage->totalAllowanceGb, 2) }} GB</p>
                </div>
                <div class="rounded-2xl bg-gray-50 p-4">
                    <p class="text-[11px] text-gray-400">الكوتا المتبقية</p>
                    <p class="font-bold text-gray-900 mt-1">{{ number_format($usage->remainingDataGb, 2) }} GB</p>
                    <p class="text-xs text-gray-500 mt-0.5">مستخدم {{ number_format($usage->usagePercentage, 1) }}%</p>
                </div>
                <div class="rounded-2xl bg-gray-50 p-4">
                    <p class="text-[11px] text-gray-400">تاريخ الانتهاء</p>
                    <p class="font-bold text-gray-900 mt-1 font-mono text-sm">{{ $subscription->expiresAt ? \Illuminate\Support\Carbon::parse($subscription->expiresAt)->format('Y-m-d') : '—' }}</p>
                </div>
                <div class="rounded-2xl bg-gray-50 p-4">
                    <p class="text-[11px] text-gray-400">حالة السلفة</p>
                    <p class="font-bold text-gray-900 mt-1">{{ $borrowLabel }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">مستخدم {{ number_format($borrowing->usedGb, 2) }} / {{ number_format($borrowing->borrowingLimitGb, 0) }} GB</p>
                </div>
            </div>

            @php
                $weeklyTotal = collect($weeklyUsage)->sum('used_gb');
                $weeklyPeak = max(0.01, (float) collect($weeklyUsage)->max('used_gb'));
                $lastSession = $sessions->first();
                $personalDetails = [
                    'رقم الهاتف' => $contractRecord?->phone ?? $sub->phone,
                    'البريد الإلكتروني' => $contractRecord?->email,
                    'رقم الهوية' => $contractRecord?->national_id,
                    'المدينة' => $contractRecord?->details['city'] ?? null,
                    'العنوان' => $contractRecord?->details['address'] ?? null,
                    'الجنسية' => $contractRecord?->details['nationality'] ?? null,
                    'طريقة السداد' => $contractRecord?->details['payment_method'] ?? null,
                    'رقم جواز السفر' => $contractRecord?->details['passport_number'] ?? null,
                ];
                $detailLabels = [
                    'first_name' => 'الاسم الأول', 'father_name' => 'اسم الأب', 'last_name' => 'اسم العائلة',
                    'automatic_renewal' => 'التجديد التلقائي', 'borrowing_enabled' => 'خدمة السلفة',
                    'ip_address' => 'عنوان IP', 'sender_device_ip' => 'IP جهاز المرسل',
                    'mac_address' => 'عنوان MAC', 'primary_dns' => 'DNS الأساسي', 'secondary_dns' => 'DNS الثانوي',
                    'device_port' => 'منفذ الجهاز', 'download_speed' => 'سرعة التحميل', 'upload_speed' => 'سرعة الرفع',
                    'start_date' => 'بداية العقد', 'end_date' => 'نهاية العقد', 'notes' => 'ملاحظات',
                ];
                $actionLabels = [
                    'SUBSCRIBER_CONTRACT_CREATED' => 'إنشاء عقد', 'SUBSCRIBER_RECHARGED' => 'شحن باقة',
                    'SUBSCRIBER_PACKAGE_CHANGED' => 'تغيير باقة', 'EMERGENCY_BORROWING_ACTIVATED' => 'تفعيل سلفة',
                    'SUBSCRIBER_CREATED' => 'إنشاء حساب مشترك',
                ];
            @endphp

            <section class="mx-6 mb-6 rounded-2xl border border-gray-100 overflow-hidden">
                <div class="border-b border-gray-100 px-5 py-4">
                    <h4 class="font-bold text-gray-900">استهلاك الأسبوع</h4>
                    <p class="mt-1 text-xs text-gray-500">الإجمالي: {{ number_format($weeklyTotal, 2) }} GB خلال آخر 7 أيام</p>
                </div>
                <div class="grid gap-3 p-5 sm:grid-cols-2 lg:grid-cols-7">
                    @forelse ($weeklyUsage as $day)
                        @php
                            $barWidth = min(100, ((float) $day['used_gb'] / $weeklyPeak) * 100);
                        @endphp
                        <div class="rounded-xl bg-gray-50 p-3">
                            <div class="flex justify-between text-[11px] text-gray-500"><span>{{ \Illuminate\Support\Carbon::parse($day['date'])->translatedFormat('D') }}</span><span>{{ number_format($day['used_gb'], 2) }} GB</span></div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-200"><div class="h-full rounded-full bg-purple-600" style="width: {{ $barWidth }}%"></div></div>
                            <p class="mt-1 text-[10px] text-gray-400">{{ $day['date'] }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">لا توجد بيانات استهلاك أسبوعية.</p>
                    @endforelse
                </div>
            </section>

            <section class="mx-6 mb-6 overflow-hidden rounded-2xl border border-gray-100">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 px-5 py-4">
                    <div><h4 class="font-bold text-gray-900">جلسات الاتصال</h4><p class="mt-1 text-xs text-gray-500">الجلسات النشطة: {{ $activeSessionCount }} · آخر اتصال: {{ $lastSession?->started_at?->format('Y-m-d H:i') ?? '—' }}</p></div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 text-gray-500"><tr><th class="px-4 py-3">الحالة</th><th class="px-4 py-3">بداية الجلسة</th><th class="px-4 py-3">نهاية الجلسة</th><th class="px-4 py-3">IP الجلسة</th><th class="px-4 py-3">MAC المعدّة</th><th class="px-4 py-3">نقطة الوصول</th><th class="px-4 py-3">استهلاك الجلسة</th></tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($sessions as $session)
                                <tr><td class="px-4 py-3">{{ $session->ended_at ? 'منتهية' : 'نشطة' }}</td><td class="px-4 py-3 font-mono">{{ $session->started_at?->format('Y-m-d H:i') }}</td><td class="px-4 py-3 font-mono">{{ $session->ended_at?->format('Y-m-d H:i') ?? '—' }}</td><td class="px-4 py-3 font-mono" dir="ltr">{{ $session->ip_address ?? '—' }}</td><td class="px-4 py-3 font-mono" dir="ltr">{{ $session->mac_address ?? '—' }}</td><td class="px-4 py-3">{{ $session->access_point ?? '—' }}</td><td class="px-4 py-3">↓ {{ number_format((float) $session->download_gb, 2) }} / ↑ {{ number_format((float) $session->upload_gb, 2) }} GB</td></tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">لا توجد جلسات مسجلة.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="mx-6 mb-6 grid gap-6 lg:grid-cols-2">
                <section class="overflow-hidden rounded-2xl border border-gray-100">
                    <div class="border-b border-gray-100 px-5 py-4"><h4 class="font-bold text-gray-900">البيانات الشخصية والعقد</h4><p class="mt-1 text-xs text-gray-500">رقم العقد: <span class="font-mono">{{ $sub->contractNumber }}</span> · الباقة: {{ $subscription->packageName }} ({{ $subscription->packageId }})</p></div>
                    <div class="grid gap-3 p-5 sm:grid-cols-2">
                        <div class="rounded-xl bg-gray-50 p-3"><p class="text-[11px] text-gray-400">تاريخ إنشاء العقد</p><p class="mt-1 text-sm font-semibold">{{ $contractRecord?->created_at?->format('Y-m-d H:i') ?? 'غير مسجل' }}</p></div>
                        <div class="rounded-xl bg-gray-50 p-3"><p class="text-[11px] text-gray-400">أنشأه</p><p class="mt-1 text-sm font-semibold">{{ $contractRecord?->creator?->name ?? 'غير مسجل' }}</p></div>
                        @foreach ($personalDetails as $label => $value)
                            @if (filled($value))<div class="rounded-xl bg-gray-50 p-3"><p class="text-[11px] text-gray-400">{{ $label }}</p><p class="mt-1 break-words text-sm font-semibold">{{ $value }}</p></div>@endif
                        @endforeach
                        @foreach (($contractRecord?->details ?? []) as $key => $value)
                            @if (filled($value) && !in_array($key, ['city','address','nationality','payment_method','passport_number'], true))<div class="rounded-xl bg-gray-50 p-3"><p class="text-[11px] text-gray-400">{{ $detailLabels[$key] ?? str_replace('_', ' ', $key) }}</p><p class="mt-1 break-words text-sm font-semibold">{{ $value }}</p></div>@endif
                        @endforeach
                        @if (!$contractRecord)<p class="text-sm text-gray-400 sm:col-span-2">لا توجد بيانات عقد تفصيلية مسجلة لهذا المشترك.</p>@endif
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-gray-100">
                    <div class="border-b border-gray-100 px-5 py-4"><h4 class="font-bold text-gray-900">الفواتير وعمليات الشحن</h4><p class="mt-1 text-xs text-gray-500">آخر {{ $payments->count() }} عملية</p></div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-right text-xs">
                            <thead class="bg-gray-50 text-gray-500"><tr><th class="px-4 py-3">التاريخ</th><th class="px-4 py-3">العملية</th><th class="px-4 py-3">الباقة</th><th class="px-4 py-3">المبلغ</th><th class="px-4 py-3">الحالة</th><th class="px-4 py-3">نفذها</th></tr></thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($payments as $payment)
                                    <tr><td class="px-4 py-3 font-mono">{{ ($payment->paid_at ?? $payment->created_at)?->format('Y-m-d H:i') }}</td><td class="px-4 py-3">{{ $payment->gatewayLabel() }}</td><td class="px-4 py-3">{{ $packages[$payment->package_id]['name'] ?? $payment->package_id ?? '—' }}</td><td class="px-4 py-3">{{ number_format((float) $payment->amount, 2) }} د.ل</td><td class="px-4 py-3">{{ $payment->statusLabel() }}</td><td class="px-4 py-3">{{ $payment->processor?->name ?? '—' }}</td></tr>
                                @empty
                                    <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">لا توجد عمليات شحن مسجلة.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <section class="mx-6 mb-6 overflow-hidden rounded-2xl border border-gray-100">
                <div class="border-b border-gray-100 px-5 py-4"><h4 class="font-bold text-gray-900">سجل إجراءات الموظفين والإدارة</h4><p class="mt-1 text-xs text-gray-500">التعديلات والعمليات المسجلة على العقد</p></div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 text-gray-500"><tr><th class="px-4 py-3">التاريخ</th><th class="px-4 py-3">الموظف</th><th class="px-4 py-3">الإجراء</th><th class="px-4 py-3">التفاصيل</th></tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($activity as $log)
                                @php
                                    $changeValues = collect($log->new_values ?? [])->except('contract_number')->all();
                                    $oldValues = collect($log->old_values ?? [])->except('contract_number')->all();
                                @endphp
                                <tr><td class="whitespace-nowrap px-4 py-3 font-mono">{{ $log->created_at?->format('Y-m-d H:i') }}</td><td class="px-4 py-3">{{ $log->user?->name ?? 'حساب محذوف' }}</td><td class="px-4 py-3 font-semibold">{{ $actionLabels[$log->action] ?? str_replace('_', ' ', $log->action) }}</td><td class="max-w-xl px-4 py-3"><div class="break-words">{{ $log->result === 'success' ? 'تمت العملية' : $log->result }}@if($oldValues) · السابق: {{ json_encode($oldValues, JSON_UNESCAPED_UNICODE) }}@endif @if($changeValues) · الجديد: {{ json_encode($changeValues, JSON_UNESCAPED_UNICODE) }}@endif</div></td></tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">لا توجد إجراءات مسجلة لهذا المشترك بعد.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            @if ($sub->equipmentIp)
                @php
                    $equipmentHost = str_contains($sub->equipmentIp, ':') ? '['.$sub->equipmentIp.']' : $sub->equipmentIp;
                @endphp
                <div class="mx-6 mb-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-sky-100 bg-sky-50 p-4">
                    <div>
                        <p class="text-[11px] font-semibold text-sky-700">IP معدّة المشترك</p>
                        <p class="mt-1 font-mono text-sm text-gray-900" dir="ltr">{{ $sub->equipmentIp }}</p>
                    </div>
                    <a href="http://{{ $equipmentHost }}" target="_blank" rel="noopener noreferrer" class="rounded-xl bg-sky-700 px-4 py-2 text-xs font-bold text-white hover:bg-sky-800">فتح إعدادات المعدّة</a>
                </div>
            @endif

            <div class="px-6 pb-6 grid grid-cols-1 lg:grid-cols-3 gap-4">
                <form method="POST" action="{{ route('staff.subscribers.recharge') }}" class="rounded-2xl border border-gray-100 p-4 space-y-3">
                    @csrf
                    <input type="hidden" name="contract_number" value="{{ $sub->contractNumber }}">
                    <p class="text-xs font-bold text-gray-800">شحن باقة</p>
                    <select name="package_id" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs">
                        @foreach ($packages as $id => $pkg)
                            <option value="{{ $id }}" @selected($id === $subscription->packageId)>{{ $pkg['name'] }} — {{ number_format($pkg['price'], 0) }} د.ل</option>
                        @endforeach
                    </select>
                    <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs">تنفيذ الشحن</button>
                </form>

                <form method="POST" action="{{ route('staff.subscribers.change-package') }}" class="rounded-2xl border border-gray-100 p-4 space-y-3">
                    @csrf
                    <input type="hidden" name="contract_number" value="{{ $sub->contractNumber }}">
                    <p class="text-xs font-bold text-gray-800">تغيير باقة</p>
                    <select name="package_id" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs">
                        @foreach ($packages as $id => $pkg)
                            <option value="{{ $id }}" @selected($id === $subscription->packageId)>{{ $pkg['name'] }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="w-full py-2 bg-[#6F42C1] hover:bg-[#5B2BB8] text-white font-bold rounded-xl text-xs">تغيير الباقة</button>
                </form>

                <form method="POST" action="{{ route('staff.subscribers.activate-borrowing') }}" class="rounded-2xl border border-gray-100 p-4 space-y-3" onsubmit="return confirm('تأكيد تفعيل سلفة الطوارئ؟');">
                    @csrf
                    <input type="hidden" name="contract_number" value="{{ $sub->contractNumber }}">
                    <p class="text-xs font-bold text-gray-800">سلفة طوارئ</p>
                    <p class="text-[11px] text-gray-500 min-h-[38px]">تفعيل فترة سماح مؤقتة للمشترك عند نفاد الكوتا أو انتهاء الاشتراك.</p>
                    <button type="submit" class="w-full py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs">تفعيل سلفة طوارئ</button>
                </form>
            </div>
            @if (auth()->user()->hasPermission('send_notifications'))
                <form method="POST" action="{{ route('staff.subscribers.notifications.send', $sub->contractNumber) }}" class="mx-6 mb-6 rounded-2xl border border-purple-100 bg-purple-50/50 p-5 space-y-3">
                    @csrf
                    <h4 class="text-sm font-bold text-gray-900">إرسال رسالة للمشترك</h4>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <input name="title" required maxlength="120" placeholder="عنوان الرسالة" class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm">
                        <textarea name="message" required maxlength="2000" rows="2" placeholder="اكتب الرسالة هنا" class="sm:col-span-2 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm"></textarea>
                    </div>
                    <button class="rounded-xl bg-[#6F42C1] px-5 py-2.5 text-xs font-bold text-white hover:bg-[#5B2BB8]">إرسال إلى بوابة المشترك</button>
                </form>
            @endif
        </div>
    @endif

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-900 text-sm">نتائج الاستعلام من مزود الخدمة</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 font-bold">
                        <th class="py-4 px-6">المشترك</th>
                        <th class="py-4 px-6">رقم العقد</th>
                        <th class="py-4 px-6">الهاتف</th>
                        <th class="py-4 px-6">الحالة</th>
                        <th class="py-4 px-6">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($results as $item)
                        <tr class="hover:bg-purple-50/20">
                            <td class="py-4 px-6 font-bold text-gray-900">{{ $item->name }}</td>
                            <td class="py-4 px-6 font-mono text-purple-700 font-bold dir-ltr text-right">{{ $item->contractNumber }}</td>
                            <td class="py-4 px-6 font-mono dir-ltr text-right">{{ $item->phone ?? '—' }}</td>
                            <td class="py-4 px-6">
                                @php
                                    $sk = match ($item->status) { 'ACTIVE' => 'active', 'EXPIRED' => 'expired', 'SUSPENDED' => 'suspended', default => 'pending' };
                                    $sl = match ($item->status) { 'ACTIVE' => 'نشط', 'EXPIRED' => 'منتهي', 'SUSPENDED' => 'موقوف', default => $item->status };
                                @endphp
                                <x-status-badge :status="$sk" :label="$sl" />
                            </td>
                            <td class="py-4 px-6">
                                <a href="{{ route('staff.subscribers.index', ['q' => $query, 'contract' => $item->contractNumber]) }}"
                                   class="text-purple-700 font-bold hover:underline">عرض الملف</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-10 text-center text-gray-400">لا توجد نتائج مطابقة.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($portalAccounts->isNotEmpty())
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-900 text-sm">حسابات بوابة المشتركين</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead>
                        <tr class="bg-gray-50 border-b text-gray-500 font-bold">
                            <th class="py-3 px-6">الاسم</th>
                            <th class="py-3 px-6">المستخدم</th>
                            <th class="py-3 px-6">رقم العقد</th>
                            <th class="py-3 px-6">الهاتف</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($portalAccounts as $account)
                            <tr>
                                <td class="py-3 px-6 font-bold">{{ $account->name }}</td>
                                <td class="py-3 px-6 font-mono">{{ $account->username }}</td>
                                <td class="py-3 px-6 font-mono">
                                    @if ($account->contract_number)
                                        <a class="text-purple-700 font-bold" href="{{ route('staff.subscribers.index', ['contract' => $account->contract_number]) }}">{{ $account->contract_number }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="py-3 px-6 font-mono">{{ $account->phone ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

@endsection
