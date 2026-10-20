@extends('layouts.app')

@section('title', 'بوابة المشترك - ' . ($user->name ?? 'حسابي'))

@section('content')
<script>window.__DASH__ = @json($dashboard);</script>

<div x-data="dashboard()" x-cloak class="space-y-6">
    <!-- Degraded Banner -->
    <div x-show="data.status === 'degraded'" class="bg-rose-50 border border-rose-200 rounded-2xl p-4 flex items-center gap-3 text-rose-800 text-sm font-bold">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <span>تعذّر جلب بيانات الخدمة حالياً، سيتم إعادة المحاولة تلقائياً.</span>
    </div>

    <!-- Hero Panel -->
    <div class="bg-[#1E1538] bg-gradient-to-br from-[#1E1538] to-[#2C1D54] rounded-2xl p-5 sm:p-6 text-white border border-white/10 relative overflow-hidden" style="background-image: linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px); background-size: 28px 28px;">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 relative z-10">
            <div class="space-y-2">
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-black">أهلاً بك، {{ $user->name }}</h1>
                    <span class="bg-white/10 border border-white/15 text-white text-xs px-3 py-1 rounded-lg font-mono" dir="ltr">{{ $contractNumber }}</span>
                </div>
                <div class="flex items-center gap-2 text-xs text-white/60">
                    <span class="w-2 h-2 rounded-full" :class="connectionDotClass" x-show="!prefersReducedMotion && connectionDotClass === 'bg-emerald-400'" class="animate-pulse"></span>
                    <span class="w-2 h-2 rounded-full" :class="connectionDotClass" x-show="prefersReducedMotion || connectionDotClass !== 'bg-emerald-400'"></span>
                    <span x-text="lastUpdateText"></span>
                    <button @click="manualRefresh" class="hover:text-white/80 transition-colors" title="تحديث يدوي">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </button>
                </div>
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <button @click="showRechargeModal = true" class="flex-1 sm:flex-none px-5 py-2.5 bg-[#8B5CF6] hover:bg-[#7C4DEF] text-white font-bold text-xs sm:text-sm rounded-xl focus-visible:ring-2 ring-cyan-300 transition-all inline-flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    شحن الباقة الآن
                </button>
                <form action="{{ route('subscriber.borrow.submit') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 bg-white/10 hover:bg-white/15 border border-white/15 text-white font-bold text-xs sm:text-sm rounded-xl focus-visible:ring-2 ring-cyan-300 transition-all inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M5.636 5.636l3.536 3.536m0 5.656l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        سلفة طوارئ
                    </button>
                </form>
            </div>
        </div>
    </div>

    <x-admin.flash />

    <!-- KPI Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Package -->
        <div class="bg-[#1E1538] border border-white/10 rounded-2xl p-5 hover:border-white/20 transition-all">
            <div class="text-[11px] uppercase tracking-wider text-white/50 mb-2">الباقة الحالية</div>
            <div class="flex items-center gap-2 mb-1">
                <h3 class="text-xl font-black text-white" x-text="data.subscription.package_name || '—'"></h3>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border"
                    :class="{
                        'bg-emerald-50 text-emerald-700 border-emerald-200/60': ['active', 'completed'].includes((data.subscription.status || 'active').toLowerCase()),
                        'bg-gray-100 text-gray-600 border-gray-200': (data.subscription.status || '').toLowerCase() === 'expired',
                        'bg-rose-50 text-rose-700 border-rose-200/60': ['failed', 'suspended'].includes((data.subscription.status || '').toLowerCase()),
                        'bg-purple-50 text-purple-700 border-purple-200': !['active', 'completed', 'expired', 'failed', 'suspended'].includes((data.subscription.status || 'active').toLowerCase())
                    }">
                    <svg x-show="['active', 'completed'].includes((data.subscription.status || 'active').toLowerCase())" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-text="data.subscription.status || 'نشط'"></span>
                </span>
            </div>
            <div class="text-xs text-white/60 font-mono" dir="ltr">
                <span x-text="data.subscription.price ? numberFormat(data.subscription.price) : '—'"></span> د.ل
            </div>
        </div>

        <!-- Remaining -->
        <div class="bg-[#1E1538] border border-white/10 rounded-2xl p-5 hover:border-white/20 transition-all">
            <div class="text-[11px] uppercase tracking-wider text-white/50 mb-2">المتبقي</div>
            <div class="text-3xl font-black text-white font-mono tabular-nums" x-text="data.usage.remaining_gb ? numberFormat(data.usage.remaining_gb) : '—'"></div>
            <div class="text-xs text-white/60 mt-1">GB</div>
        </div>

        <!-- Days Left -->
        <div class="bg-[#1E1538] border border-white/10 rounded-2xl p-5 hover:border-white/20 transition-all">
            <div class="text-[11px] uppercase tracking-wider text-white/50 mb-2">أيام حتى الانتهاء</div>
            <div class="text-3xl font-black text-white font-mono tabular-nums" x-text="data.subscription.days_left !== null ? data.subscription.days_left : '—'"></div>
            <div class="text-xs text-white/60 mt-1" dir="ltr" x-text="data.subscription.expires_at || '—'"></div>
        </div>

        <!-- Avg Daily -->
        <div class="bg-[#1E1538] border border-white/10 rounded-2xl p-5 hover:border-white/20 transition-all">
            <div class="text-[11px] uppercase tracking-wider text-white/50 mb-2">متوسط الاستهلاك اليومي</div>
            <div class="text-3xl font-black text-white font-mono tabular-nums" x-text="data.insights.avg_daily_gb ? numberFormat(data.insights.avg_daily_gb) : '—'"></div>
            <div class="text-xs text-white/60 mt-1">GB / يوم (آخر 7 أيام)</div>
        </div>
    </div>

    <!-- Main Row -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <!-- Quota Gauge -->
        <div class="lg:col-span-2 bg-[#1E1538] border border-white/10 rounded-2xl p-6 hover:border-white/20 transition-all">
            <div class="text-[11px] uppercase tracking-wider text-white/50 mb-4">استهلاك الحصة</div>
            <div class="flex flex-col items-center">
                <!-- Circular Gauge -->
                <div class="relative w-48 h-48 mb-4">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="42" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="8"/>
                        <circle cx="50" cy="50" r="42" fill="none" :stroke="gaugeColor" stroke-width="8"
                            stroke-linecap="round"
                            :stroke-dasharray="circumference"
                            :stroke-dashoffset="gaugeOffset"
                            class="transition-all duration-500 ease-out"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <div class="text-4xl font-black text-white font-mono tabular-nums" x-text="data.usage.percent ? numberFormat(data.usage.percent) : '—'"></div>
                        <div class="text-xs text-white/60 mt-1">مستهلك</div>
                    </div>
                </div>
                <!-- Usage Details -->
                <div class="grid grid-cols-3 gap-4 w-full text-center">
                    <div>
                        <div class="text-lg font-bold text-white font-mono tabular-nums" x-text="data.usage.used_gb ? numberFormat(data.usage.used_gb) : '—'"></div>
                        <div class="text-[10px] text-white/50 uppercase">مستخدم</div>
                    </div>
                    <div>
                        <div class="text-lg font-bold text-white font-mono tabular-nums" x-text="data.usage.remaining_gb ? numberFormat(data.usage.remaining_gb) : '—'"></div>
                        <div class="text-[10px] text-white/50 uppercase">متبقي</div>
                    </div>
                    <div>
                        <div class="text-lg font-bold text-white font-mono tabular-nums" x-text="data.usage.total_gb ? numberFormat(data.usage.total_gb) : '—'"></div>
                        <div class="text-[10px] text-white/50 uppercase">إجمالي</div>
                    </div>
                </div>
            </div>
            <!-- Borrowing Status -->
            <div x-show="data.borrowing.status === 'ACTIVE'" class="mt-4 bg-amber-500/10 border border-amber-500/30 rounded-xl p-3 text-xs text-amber-300 font-bold text-center">
                <span>سلفة الطوارئ مفعّلة — المستخدم <span dir="ltr" class="font-mono" x-text="numberFormat(data.borrowing.used_gb)"></span> من <span dir="ltr" class="font-mono" x-text="numberFormat(data.borrowing.limit_gb)"></span> GB — تنتهي <span dir="ltr" x-text="data.borrowing.expires_at"></span></span>
            </div>
        </div>

        <!-- Daily Usage Chart -->
        <div class="lg:col-span-3 bg-[#1E1538] border border-white/10 rounded-2xl p-6 hover:border-white/20 transition-all">
            <div class="text-[11px] uppercase tracking-wider text-white/50 mb-4">الاستهلاك اليومي (آخر 30 يوم)</div>
            <div class="relative h-64">
                <canvas id="usageChart"></canvas>
                <div x-show="data.history.length === 0" class="absolute inset-0 flex items-center justify-center text-white/40 text-sm">
                    لا توجد بيانات استهلاك بعد
                </div>
            </div>
        </div>
    </div>

    <!-- Insight Strip -->
    <div class="bg-[#1E1538]/80 backdrop-blur border border-white/10 rounded-2xl px-5 py-4">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" :class="insightIconClass" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-sm font-bold" :class="insightTextColor" x-text="insightText"></span>
        </div>
    </div>

    <!-- History Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Payments History -->
        <div class="bg-white border border-gray-100 rounded-2xl p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-sm text-gray-900 inline-flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg>
                    سجل المعاملات والمدفوعات
                </h3>
                <span class="text-xs text-gray-400 font-mono">{{ $payments->count() }} معاملة</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 font-bold border-b border-gray-100">
                            <th class="py-2.5 px-3">المرجع</th>
                            <th class="py-2.5 px-3">البوابة</th>
                            <th class="py-2.5 px-3">المبلغ</th>
                            <th class="py-2.5 px-3">التاريخ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($payments as $p)
                            <tr>
                                <td class="py-3 px-3 font-mono text-purple-700 font-bold">{{ $p->reference }}</td>
                                <td class="py-3 px-3 font-bold">{{ $p->gatewayLabel() }}</td>
                                <td class="py-3 px-3 font-mono text-emerald-700 font-black">{{ number_format($p->amount, 2) }} د.ل</td>
                                <td class="py-3 px-3 font-mono text-gray-400">{{ ($p->paid_at ?: $p->created_at)?->format('Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-gray-400">لا توجد عمليات دفع مسجلة.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Complaints History -->
        <div class="bg-white border border-gray-100 rounded-2xl p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-sm text-gray-900 inline-flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    تذاكر الشكاوى والدعم
                </h3>
                <button @click="showComplaintModal = true" class="px-3 py-1.5 bg-purple-100 hover:bg-purple-200 text-purple-800 font-bold text-xs rounded-xl transition-all">
                    + تقديم شكوى
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 font-bold border-b border-gray-100">
                            <th class="py-2.5 px-3">الموضوع</th>
                            <th class="py-2.5 px-3">النوع</th>
                            <th class="py-2.5 px-3">التاريخ</th>
                            <th class="py-2.5 px-3">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($complaints as $c)
                            <tr>
                                <td class="py-3 px-3 font-bold">{{ $c->subject }}</td>
                                <td class="py-3 px-3 text-gray-500">{{ $c->type ?: 'عام' }}</td>
                                <td class="py-3 px-3 font-mono text-gray-400">{{ $c->created_at->format('Y-m-d') }}</td>
                                <td class="py-3 px-3">
                                    <x-status-badge :status="$c->status" :label="$c->status" />
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-gray-400">لا توجد شكاوى مسجلة.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recharge Modal -->
    <div x-show="showRechargeModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-5 border border-purple-100" @click.away="showRechargeModal = false">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-base text-gray-900 inline-flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    اختيار باقة الشحن
                </h3>
                <button @click="showRechargeModal = false" class="text-gray-400 hover:text-gray-600 font-bold">&times;</button>
            </div>
            <form action="{{ route('subscriber.recharge.submit') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">الباقات المتاحة</label>
                    <div class="space-y-2">
                        @foreach ($packages as $pkgId => $pkg)
                            <label class="flex items-center justify-between p-3 border border-gray-200 rounded-xl hover:border-purple-500 cursor-pointer transition-all">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="package_id" value="{{ $pkgId }}" @checked($loop->first) class="text-purple-600 focus:ring-purple-500">
                                    <div>
                                        <p class="font-bold text-xs text-gray-900">{{ $pkg['name'] }}</p>
                                        <p class="text-[10px] text-gray-400 font-mono">السرعة: {{ $pkg['speed'] }}</p>
                                    </div>
                                </div>
                                <span class="font-bold text-xs text-purple-700 font-mono">{{ $pkg['price'] }} د.ل</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                    <button type="button" @click="showRechargeModal = false" class="px-4 py-2 bg-gray-100 text-gray-600 text-xs font-bold rounded-xl">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        تأكيد الشحن
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Complaint Modal -->
    <div x-show="showComplaintModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-5 border border-purple-100" @click.away="showComplaintModal = false">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-base text-gray-900 inline-flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    تقديم شكوى أو تذكرة دعم
                </h3>
                <button @click="showComplaintModal = false" class="text-gray-400 hover:text-gray-600 font-bold">&times;</button>
            </div>
            <form action="{{ route('subscriber.complaints.submit') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">موضوع الشكوى</label>
                    <input type="text" name="subject" placeholder="مثال: بطء في سرعة التنزيل" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">نوع المشكلة</label>
                    <select name="type" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="بطء الإنترنت">بطء الإنترنت</option>
                        <option value="انقطاع الخدمة">انقطاع الخدمة</option>
                        <option value="استفسار عن الفواتير">استفسار عن الفواتير والمدفوعات</option>
                        <option value="مشكلة في جهاز الراوتر">مشكلة في الراوتر / المنظومة</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">تفاصيل إضافية</label>
                    <textarea name="description" rows="3" placeholder="اكتب تفاصيل المشكلة..." class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                    <button type="button" @click="showComplaintModal = false" class="px-4 py-2 bg-gray-100 text-gray-600 text-xs font-bold rounded-xl">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl shadow-md">إرسال التذكرة</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
<script>
function dashboard() {
    return {
        data: window.__DASH__ || {
            status: 'degraded',
            subscription: { package_name: null, price: null, status: null, expires_at: null, days_left: null },
            usage: { total_gb: 0, used_gb: 0, remaining_gb: 0, percent: 0 },
            borrowing: { status: null, used_gb: 0, limit_gb: 0, expires_at: null },
            history: [],
            insights: { avg_daily_gb: 0, projected_depletion_date: null, will_last_until_expiry: null }
        },
        showRechargeModal: false,
        showComplaintModal: false,
        pollingInterval: 30000,
        backoffInterval: 30000,
        maxBackoff: 300000,
        pollTimer: null,
        lastUpdateAt: Date.now(),
        prefersReducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
        chart: null,

        get circumference() {
            return 2 * Math.PI * 42;
        },

        get gaugeOffset() {
            if (this.data.status === 'degraded' || this.data.usage.percent === null) return this.circumference;
            return this.circumference - (this.data.usage.percent / 100) * this.circumference;
        },

        get gaugeColor() {
            if (this.data.status === 'degraded') return 'rgba(255,255,255,0.2)';
            const p = this.data.usage.percent || 0;
            if (p < 60) return '#22D3EE';
            if (p < 85) return '#FBBF24';
            return '#FB7185';
        },

        get connectionDotClass() {
            if (document.hidden) return 'bg-gray-400';
            if (this.data.status === 'degraded') return 'bg-amber-400';
            return 'bg-emerald-400';
        },

        get lastUpdateText() {
            const seconds = Math.floor((Date.now() - this.lastUpdateAt) / 1000);
            if (seconds < 60) return 'آخر تحديث: منذ ' + seconds + ' ثانية';
            const minutes = Math.floor(seconds / 60);
            return 'آخر تحديث: منذ ' + minutes + ' دقيقة';
        },

        get insightText() {
            const i = this.data.insights;
            if (i.will_last_until_expiry === true) return 'استهلاكك متوازن، حصتك تكفي حتى نهاية الاشتراك.';
            if (i.will_last_until_expiry === false) return 'بمعدلك الحالي ستنفد الحصة تقريباً في ' + i.projected_depletion_date + '، قبل انتهاء الاشتراك.';
            return 'لا تتوفر بيانات كافية للتوقع.';
        },

        get insightTextColor() {
            const i = this.data.insights;
            if (i.will_last_until_expiry === true) return 'text-emerald-400';
            if (i.will_last_until_expiry === false) return 'text-amber-400';
            return 'text-gray-400';
        },

        get insightIconClass() {
            const i = this.data.insights;
            if (i.will_last_until_expiry === true) return 'text-emerald-400';
            if (i.will_last_until_expiry === false) return 'text-amber-400';
            return 'text-gray-400';
        },

        numberFormat(num) {
            if (num === null || num === undefined) return '—';
            return parseFloat(num).toFixed(2);
        },

        init() {
            this.updateLastUpdate();
            setInterval(() => this.updateLastUpdate(), 1000);
            this.initChart();
            this.schedulePoll();

            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) {
                    this.manualRefresh();
                }
            });
        },

        updateLastUpdate() {
            const now = Date.now();
            const elapsed = now - this.lastUpdateAt;
            if (elapsed > 86400000) {
                this.lastUpdateAt = now;
            }
        },

        initChart() {
            if (typeof Chart === 'undefined' || this.data.history.length === 0) return;

            const ctx = document.getElementById('usageChart');
            if (!ctx) return;

            const labels = this.data.history.map(h => h.date);
            const data = this.data.history.map(h => h.used_gb);
            const lastIndex = data.length - 1;

            this.chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: data.map((_, i) => i === lastIndex ? '#22D3EE' : '#8B5CF6'),
                        borderRadius: 4,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            rtl: true,
                            callbacks: {
                                label: (ctx) => ctx.raw + ' GB'
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: false
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255,255,255,0.1)',
                                drawBorder: false
                            },
                            ticks: {
                                color: 'rgba(255,255,255,0.5)',
                                font: { size: 10 }
                            }
                        }
                    },
                    animation: this.prefersReducedMotion ? false : { duration: 300 }
                }
            });
        },

        updateChart() {
            if (!this.chart || this.data.history.length === 0) return;

            const labels = this.data.history.map(h => h.date);
            const data = this.data.history.map(h => h.used_gb);
            const lastIndex = data.length - 1;

            this.chart.data.labels = labels;
            this.chart.data.datasets[0].data = data;
            this.chart.data.datasets[0].backgroundColor = data.map((_, i) => i === lastIndex ? '#22D3EE' : '#8B5CF6');
            this.chart.update('none');
        },

        schedulePoll() {
            if (this.pollTimer) clearTimeout(this.pollTimer);
            this.pollTimer = setTimeout(() => this.poll(), this.backoffInterval);
        },

        async poll() {
            if (document.hidden) {
                this.schedulePoll();
                return;
            }

            try {
                const response = await fetch('{{ route('subscriber.dashboard.data') }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                if (response.status === 401 || response.status === 419) {
                    location.reload();
                    return;
                }

                if (!response.ok) throw new Error('Network error');

                const newData = await response.json();
                this.data = newData;
                this.lastUpdateAt = Date.now();
                this.backoffInterval = 30000;
                this.updateChart();
            } catch (e) {
                this.backoffInterval = Math.min(this.backoffInterval * 2, this.maxBackoff);
            }

            this.schedulePoll();
        },

        manualRefresh() {
            this.backoffInterval = 30000;
            if (this.pollTimer) clearTimeout(this.pollTimer);
            this.poll();
        }
    };
}
</script>
@endsection
