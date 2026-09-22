@extends('layouts.app')

@section('title', 'إدارة المشتركين')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
        <div>
            <h2 class="text-xl font-black text-gray-900">إدارة المشتركين</h2>
            <p class="text-xs text-gray-400 mt-1">استعلام سريع برقم العقد أو الاسم أو الهاتف — البيانات من مزود المشتركين مباشرة</p>
        </div>
        <button id="openSubscriberModal" type="button" class="px-4 py-2.5 bg-[#6F42C1] hover:bg-[#5B2BB8] text-white font-bold rounded-xl text-xs shadow-md transition flex items-center gap-2 self-start sm:self-auto cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>إضافة حساب بوابة</span>
        </button>
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

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 p-6">
                <div class="rounded-2xl bg-gray-50 p-4">
                    <p class="text-[11px] text-gray-400">الباقة الحالية</p>
                    <p class="font-bold text-gray-900 mt-1">{{ $subscription->packageName }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ number_format($subscription->packagePrice, 2) }} د.ل</p>
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

<div id="subscriberModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 md:p-8 space-y-6 shadow-2xl relative text-right">
        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
            <h3 class="text-lg font-bold text-gray-900">إضافة حساب مشترك في البوابة</h3>
            <button id="closeSubscriberModal" type="button" class="text-gray-400 hover:text-gray-600 text-xl font-bold p-1">×</button>
        </div>
        <form method="POST" action="{{ route('staff.subscribers.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">الاسم الكامل</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">اسم المستخدم</label>
                <input type="text" name="username" value="{{ old('username') }}" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-mono dir-ltr text-right">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">رقم الهاتف</label>
                <input type="text" name="phone" value="{{ old('phone') }}" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-mono dir-ltr text-right">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold mb-1">كلمة المرور</label>
                    <input type="password" name="password" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">التأكيد</label>
                    <input type="password" name="password_confirmation" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs">
                </div>
            </div>
            <div class="pt-4 flex justify-end gap-3 border-t">
                <button id="cancelSubscriberModal" type="button" class="px-5 py-2.5 bg-gray-100 rounded-xl text-xs font-semibold">إلغاء</button>
                <button type="submit" class="px-6 py-2.5 bg-[#6F42C1] text-white font-bold rounded-xl text-xs">إنشاء الحساب</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('subscriberModal');
        const open = () => modal.classList.remove('hidden');
        const close = () => modal.classList.add('hidden');
        document.getElementById('openSubscriberModal')?.addEventListener('click', open);
        document.getElementById('closeSubscriberModal')?.addEventListener('click', close);
        document.getElementById('cancelSubscriberModal')?.addEventListener('click', close);
        @if ($errors->any()) open(); @endif
    });
</script>
@endpush
