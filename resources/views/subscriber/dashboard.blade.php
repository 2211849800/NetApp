@extends('layouts.app')

@section('title', 'بوابة المشترك - ' . ($user->name ?? 'حسابي'))

@section('content')
<div class="space-y-6" x-data="{ showRechargeModal: false, showComplaintModal: false }">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-purple-700 via-indigo-700 to-purple-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden">
        <div class="space-y-2 z-10">
            <span class="bg-white/20 text-white text-xs px-3 py-1 rounded-full font-mono font-bold">رقم العقد: {{ $contractNumber }}</span>
            <h1 class="text-2xl sm:text-3xl font-black">أهلاً بك، {{ $user->name }}</h1>
            <p class="text-purple-100 text-xs sm:text-sm">مرحباً بك في بوابة المشترك الخاصة بشركة الإنترنت. يمكنك متابعة رصيدك واستهلاكك وشحن باقتك بسهولة.</p>
        </div>
        <div class="flex items-center gap-3 z-10 w-full sm:w-auto">
            <button @click="showRechargeModal = true" class="flex-1 sm:flex-none px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs sm:text-sm rounded-2xl shadow-lg transition-all text-center inline-flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                شحن الباقة الآن
            </button>
            <form action="{{ route('subscriber.borrow.submit') }}" method="POST">
                @csrf
                <button type="submit" class="px-5 py-3 bg-white/10 hover:bg-white/20 border border-white/30 text-white font-bold text-xs sm:text-sm rounded-2xl transition-all inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M5.636 5.636l3.536 3.536m0 5.656l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    سلفة طوارئ
                </button>
            </form>
        </div>
    </div>

    <x-admin.flash />

    <!-- Status & Quota Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Package Info Card -->
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400">الباقة الحالية</span>
                <x-status-badge :status="strtolower($subscription?->status ?? 'ACTIVE')" :label="$subscription?->status ?? 'نشط'" />
            </div>
            <div>
                <h3 class="text-2xl font-black text-purple-900">{{ $subscription?->packageName ?? 'ميقا 70' }}</h3>
                <p class="text-xs text-gray-500 font-mono mt-1">السعر: {{ $subscription?->packagePrice ?? 70 }} د.ل / 30 يوم</p>
            </div>
            <div class="border-t pt-3 flex justify-between text-xs text-gray-500 font-mono">
                <span>تاريخ الانتهاء:</span>
                <span class="font-bold text-gray-800">{{ $subscription?->expiresAt ? \Carbon\Carbon::parse($subscription->expiresAt)->format('Y-m-d') : 'مفتوح' }}</span>
            </div>
        </div>

        <!-- Quota Usage Card -->
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4 md:col-span-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400">استهلاك حصة البيانات (Quota)</span>
                <span class="text-xs font-mono font-bold text-purple-700">{{ number_format($usage?->usagePercentage ?? 45, 1) }}% مستهلك</span>
            </div>

            <!-- Progress Bar -->
            <div class="space-y-2">
                <div class="w-full bg-gray-100 rounded-full h-4 overflow-hidden p-0.5 border">
                    <div class="bg-gradient-to-r from-purple-500 to-indigo-600 h-full rounded-full transition-all duration-500" style="width: {{ min(100, $usage?->usagePercentage ?? 45) }}%"></div>
                </div>
                <div class="flex justify-between text-xs font-mono text-gray-500 pt-1">
                    <span>المستخدم: <strong class="text-purple-900">{{ number_format($usage?->usedDataGb ?? 35, 2) }} GB</strong></span>
                    <span>المتبقي: <strong class="text-emerald-600">{{ number_format($usage?->remainingDataGb ?? 44, 2) }} GB</strong></span>
                    <span>إجمالي الحصة: <strong>{{ number_format($usage?->totalAllowanceGb ?? 79, 0) }} GB</strong></span>
                </div>
            </div>

            <!-- Borrowing Status banner -->
            @if (($borrowing?->status ?? '') === 'ACTIVE')
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-3 flex items-center justify-between text-xs text-amber-800 font-bold">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        سلفة الطوارئ مفعّلة حالياً (استهلاك السلفة: {{ number_format($borrowing->usedGb, 2) }} GB)
                    </span>
                    <span class="font-mono text-amber-600">تنتهي: {{ \Carbon\Carbon::parse($borrowing->expiresAt)->format('Y-m-d') }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- History Tables (Payments & Complaints) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Payments History -->
        <div class="bg-white rounded-3xl border border-gray-100 p-6 space-y-4 shadow-sm">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="font-bold text-sm text-gray-900 inline-flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg>
                    سجل المعاملات والمدفوعات
                </h3>
                <span class="text-xs text-gray-400 font-mono">{{ $payments->count() }} معاملة</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 font-bold border-b">
                            <th class="py-2.5 px-3">المرجع</th>
                            <th class="py-2.5 px-3">البوابة</th>
                            <th class="py-2.5 px-3">المبلغ</th>
                            <th class="py-2.5 px-3">التاريخ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
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

        <!-- Complaints History & New Complaint Button -->
        <div class="bg-white rounded-3xl border border-gray-100 p-6 space-y-4 shadow-sm">
            <div class="flex items-center justify-between border-b pb-3">
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
                        <tr class="bg-gray-50 text-gray-500 font-bold border-b">
                            <th class="py-2.5 px-3">الموضوع</th>
                            <th class="py-2.5 px-3">النوع</th>
                            <th class="py-2.5 px-3">التاريخ</th>
                            <th class="py-2.5 px-3">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
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
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 space-y-5 border border-purple-100" @click.away="showRechargeModal = false">
            <div class="flex items-center justify-between border-b pb-3">
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
                            <label class="flex items-center justify-between p-3 border rounded-2xl hover:border-purple-500 cursor-pointer transition-all">
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
                <div class="flex items-center justify-end gap-3 pt-3 border-t">
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
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 space-y-5 border border-purple-100" @click.away="showComplaintModal = false">
            <div class="flex items-center justify-between border-b pb-3">
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
                    <input type="text" name="subject" placeholder="مثال: بطء في سرعة التنزيل" required class="w-full px-4 py-2.5 rounded-xl border text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">نوع المشكلة</label>
                    <select name="type" class="w-full px-4 py-2.5 rounded-xl border text-xs">
                        <option value="بطء الإنترنت">بطء الإنترنت</option>
                        <option value="انقطاع الخدمة">انقطاع الخدمة</option>
                        <option value="استفسار عن الفواتير">استفسار عن الفواتير والمدفوعات</option>
                        <option value="مشكلة في جهاز الراوتر">مشكلة في الراوتر / المنظومة</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">تفاصيل إضافية</label>
                    <textarea name="description" rows="3" placeholder="اكتب تفاصيل المشكلة..." class="w-full px-4 py-2.5 rounded-xl border text-xs"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t">
                    <button type="button" @click="showComplaintModal = false" class="px-4 py-2 bg-gray-100 text-gray-600 text-xs font-bold rounded-xl">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl shadow-md">إرسال التذكرة</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
