@extends('layouts.app')

@section('title', 'سجل رصيد المقبوضات الشخصي')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <x-admin.page-header title="سجل رصيد المقبوضات الشخصي" subtitle="متابعة حركات المقبوضات المالية والتحويلات المصرفية المعالجة بواسطة حسابك" />
    </div>

    <x-admin.flash />

    <!-- Filter Form -->
    <form method="GET" class="bg-white p-4 rounded-3xl border border-gray-100 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="text-xs font-bold text-gray-600">اختر الشهر:</span>
            <input type="month" name="month" value="{{ $month }}" class="px-4 py-2 rounded-xl border text-xs font-mono">
            <button type="submit" class="px-4 py-2 bg-purple-600 text-white font-bold text-xs rounded-xl">عرض الكشف</button>
        </div>
        <div class="text-xs font-mono text-purple-700 bg-purple-50 px-3 py-1.5 rounded-xl border border-purple-100 font-bold">
            الموظف: {{ $user->name }}
        </div>
    </form>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-3xl p-5 border border-purple-100 shadow-sm space-y-2">
            <span class="text-xs text-gray-400 font-bold">إجمالي مقبوضات الشهر</span>
            <p class="text-2xl font-black text-purple-900 font-mono">{{ number_format($stats['total_balance'], 2) }} د.ل</p>
            <p class="text-[11px] text-purple-600 font-bold">حركات معتمدة بواسطة حسابك</p>
        </div>


        <div class="bg-white rounded-3xl p-5 border border-emerald-100 shadow-sm space-y-2">
            <span class="text-xs text-gray-400 font-bold">المقبوضات النقدية (Cash)</span>
            <p class="text-2xl font-black text-emerald-700 font-mono">{{ number_format($stats['total_cash'], 2) }} د.ل</p>
            <p class="text-[11px] text-gray-500">نقدي في الخزينة الفردية</p>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-blue-100 shadow-sm space-y-2">
            <span class="text-xs text-gray-400 font-bold">التحويلات المصرفية المعتمدة</span>
            <p class="text-2xl font-black text-blue-700 font-mono">{{ number_format($stats['total_bank'], 2) }} د.ل</p>
            <p class="text-[11px] text-gray-500">تم التأكد منها وإضافتها لرصيدك</p>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm space-y-2">
            <span class="text-xs text-gray-400 font-bold">عدد المعاملات المعالجة</span>
            <p class="text-2xl font-black text-gray-900 font-mono">{{ $stats['total_count'] }} معاملة</p>
            <p class="text-[11px] text-emerald-600 font-bold inline-flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                محمية من التزوير والتكرار
            </p>
        </div>
    </div>

    <!-- الصندوق اليومي (نقدي / بطاقة) — مستقل عن جدول Payment -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-6" id="daily-register-section"
        data-store-url="{{ route('staff.my-balance.entries.store') }}"
        data-close-url="{{ route('staff.my-balance.close') }}"
        data-staff-name="{{ $user->name }}"
        data-cash-total="{{ number_format($openCashTotal, 2, '.', '') }}"
        data-card-total="{{ number_format($openCardTotal, 2, '.', '') }}"
        data-transfers-total="{{ number_format($openTransfersTotal, 2, '.', '') }}"
    >
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b pb-3">
            <div>
                <h3 class="font-bold text-sm text-gray-900">الصندوق اليومي (نقدي / بطاقة)</h3>
                <p class="text-[11px] text-gray-400 mt-1">إضافات تراكمية محفوظة لصندوقك فقط — التحويلات تُقرأ من المقبوضات دون تعديلها</p>
            </div>
            @if ($lastClosure)
                <span class="text-[11px] font-mono text-gray-400 bg-gray-50 px-3 py-1.5 rounded-xl border border-gray-100">
                    آخر إغلاق: {{ $lastClosure->closed_at?->format('Y-m-d H:i') }} — {{ number_format((float) $lastClosure->grand_total, 2) }} د.ل
                </span>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/30 p-4 space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold text-emerald-800">نقدي (Cash)</h4>
                    <p class="text-sm font-black font-mono text-emerald-800"><span id="open-cash-total">{{ number_format($openCashTotal, 2) }}</span> د.ل</p>
                </div>
                <form id="cash-entry-form" class="flex gap-2" data-type="cash">
                    <input type="number" name="amount" step="0.01" min="0.01" required placeholder="0.00"
                        class="flex-1 px-3 py-2 rounded-xl border border-emerald-200 bg-white text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-200">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl">إضافة</button>
                </form>
                <ul id="cash-entry-list" class="divide-y divide-emerald-100 max-h-56 overflow-y-auto text-xs">
                    @forelse ($openCashEntries as $entry)
                        <li class="flex items-center justify-between py-2">
                            <span class="font-mono text-gray-400">{{ $entry->created_at?->format('Y-m-d H:i:s') }}</span>
                            <span class="font-mono font-bold text-emerald-800">{{ number_format((float) $entry->amount, 2) }} د.ل</span>
                        </li>
                    @empty
                        <li class="empty-row py-4 text-center text-gray-400">لا توجد إضافات نقدية مفتوحة</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-2xl border border-blue-100 bg-blue-50/30 p-4 space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold text-blue-800">بطاقة (Card)</h4>
                    <p class="text-sm font-black font-mono text-blue-800"><span id="open-card-total">{{ number_format($openCardTotal, 2) }}</span> د.ل</p>
                </div>
                <form id="card-entry-form" class="flex gap-2" data-type="card">
                    <input type="number" name="amount" step="0.01" min="0.01" required placeholder="0.00"
                        class="flex-1 px-3 py-2 rounded-xl border border-blue-200 bg-white text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-200">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl">إضافة</button>
                </form>
                <ul id="card-entry-list" class="divide-y divide-blue-100 max-h-56 overflow-y-auto text-xs">
                    @forelse ($openCardEntries as $entry)
                        <li class="flex items-center justify-between py-2">
                            <span class="font-mono text-gray-400">{{ $entry->created_at?->format('Y-m-d H:i:s') }}</span>
                            <span class="font-mono font-bold text-blue-800">{{ number_format((float) $entry->amount, 2) }} د.ل</span>
                        </li>
                    @empty
                        <li class="empty-row py-4 text-center text-gray-400">لا توجد إضافات بطاقة مفتوحة</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="rounded-2xl border border-purple-100 bg-purple-50/40 p-4 grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <span class="text-[11px] text-gray-400 font-bold">إجمالي النقدي المفتوح</span>
                <p class="font-mono font-black text-emerald-800"><span id="summary-cash">{{ number_format($openCashTotal, 2) }}</span> د.ل</p>
            </div>
            <div>
                <span class="text-[11px] text-gray-400 font-bold">إجمالي البطاقة المفتوح</span>
                <p class="font-mono font-black text-blue-800"><span id="summary-card">{{ number_format($openCardTotal, 2) }}</span> د.ل</p>
            </div>
            <div>
                <span class="text-[11px] text-gray-400 font-bold">التحويلات منذ آخر إغلاق</span>
                <p class="font-mono font-black text-purple-800"><span id="summary-transfers">{{ number_format($openTransfersTotal, 2) }}</span> د.ل</p>
            </div>
            <div>
                <span class="text-[11px] text-gray-400 font-bold">الإجمالي المتوقع عند الإغلاق</span>
                <p class="font-mono font-black text-gray-900"><span id="summary-grand">{{ number_format($openCashTotal + $openCardTotal + $openTransfersTotal, 2) }}</span> د.ل</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-[11px] text-gray-400">الإغلاق يوثّق الدورة الحالية في قاعدة البيانات ويبدأ صندوقًا جديدًا من صفر.</p>
            <button type="button" id="close-account-btn" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-sm transition-colors">
                إغلاق الحساب
            </button>
        </div>
    </div>

    <div id="close-account-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" data-close-modal></div>
        <div class="relative bg-white rounded-3xl border border-gray-100 shadow-xl max-w-md w-full p-6 space-y-4">
            <h3 class="font-bold text-sm text-gray-900">تأكيد إغلاق الحساب</h3>
            <p id="close-account-modal-text" class="text-xs text-gray-600 leading-6"></p>
            <p id="close-account-modal-error" class="hidden text-xs text-red-600"></p>
            <div class="flex items-center justify-end gap-2">
                <button type="button" data-close-modal class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50">إلغاء</button>
                <button type="button" id="confirm-close-account-btn" class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold">تأكيد الإغلاق</button>
            </div>
        </div>
    </div>

    <!-- Ledger Transactions Table -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden space-y-4 p-6">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="font-bold text-sm text-gray-900">سجل المعاملات المضافة لرصيدي (حظر التكرار والتزوير)</h3>
            <span class="text-xs font-mono text-gray-400">{{ $payments->count() }} حركة مسجلة</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 font-bold border-b">
                        <th class="py-3 px-4">رقم المعاملة</th>
                        <th class="py-3 px-4">المشترك</th>
                        <th class="py-3 px-4">طريقة الدفع / البوابة</th>
                        <th class="py-3 px-4">المبلغ</th>
                        <th class="py-3 px-4">التاريخ والوقت</th>
                        <th class="py-3 px-4">حالة الحماية والأمان</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($payments as $p)
                        <tr class="hover:bg-purple-50/20">
                            <td class="py-3.5 px-4 font-mono text-purple-800 font-bold">{{ $p->reference }}</td>
                            <td class="py-3.5 px-4">
                                <p class="font-bold text-gray-900">{{ $p->subscriber_name ?: 'مشترك' }}</p>
                                <p class="text-[11px] font-mono text-gray-400">{{ $p->contract_number ?: '—' }}</p>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $p->gateway === 'cash' ? 'bg-emerald-50 text-emerald-800' : 'bg-blue-50 text-blue-800' }}">
                                    {{ $p->gatewayLabel() }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-emerald-700 font-black text-sm">{{ number_format($p->amount, 2) }} د.ل</td>
                            <td class="py-3.5 px-4 font-mono text-gray-400">{{ $p->paid_at?->format('Y-m-d H:i:s') }}</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full font-bold text-[11px]">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    مضاف لرصيد ({{ $user->name }})
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-10 text-center text-gray-400">لا توجد معاملات مضافة لرصيدك في هذا الشهر.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const section = document.getElementById('daily-register-section');
    if (!section) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const storeUrl = section.dataset.storeUrl;
    const closeUrl = section.dataset.closeUrl;
    const modal = document.getElementById('close-account-modal');
    const modalText = document.getElementById('close-account-modal-text');
    const modalError = document.getElementById('close-account-modal-error');
    const confirmBtn = document.getElementById('confirm-close-account-btn');

    const state = {
        cash: parseFloat(section.dataset.cashTotal || '0') || 0,
        card: parseFloat(section.dataset.cardTotal || '0') || 0,
        transfers: parseFloat(section.dataset.transfersTotal || '0') || 0,
    };

    function formatAmount(n) {
        return (parseFloat(n) || 0).toFixed(2);
    }

    function formatDisplay(n) {
        return Number(formatAmount(n)).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = formatDisplay(value);
    }

    function updateSummary() {
        const grand = state.cash + state.card + state.transfers;
        setText('open-cash-total', state.cash);
        setText('open-card-total', state.card);
        setText('summary-cash', state.cash);
        setText('summary-card', state.card);
        setText('summary-transfers', state.transfers);
        setText('summary-grand', grand);
    }

    function removeEmptyRow(list) {
        const empty = list.querySelector('.empty-row');
        if (empty) empty.remove();
    }

    function prependEntry(type, entry) {
        const list = document.getElementById(type === 'cash' ? 'cash-entry-list' : 'card-entry-list');
        if (!list) return;
        removeEmptyRow(list);
        const li = document.createElement('li');
        li.className = 'flex items-center justify-between py-2';
        const amountClass = type === 'cash' ? 'text-emerald-800' : 'text-blue-800';
        li.innerHTML = '<span class="font-mono text-gray-400">' + escapeHtml(entry.created_at || '') +
            '</span><span class="font-mono font-bold ' + amountClass + '">' + formatDisplay(entry.amount) + ' د.ل</span>';
        list.prepend(li);
    }

    async function parseError(response) {
        try {
            const data = await response.json();
            if (data.errors) {
                return Object.values(data.errors).flat().join(' ');
            }
            return data.message || 'تعذر إتمام العملية.';
        } catch (e) {
            return 'تعذر إتمام العملية.';
        }
    }

    async function addEntry(type, amount, form) {
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        try {
            const response = await fetch(storeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ type: type, amount: amount }),
            });

            if (!response.ok) {
                window.alert(await parseError(response));
                return;
            }

            const data = await response.json();
            prependEntry(type, data.entry);
            state.cash = parseFloat(data.open_cash_total) || 0;
            state.card = parseFloat(data.open_card_total) || 0;
            updateSummary();
            form.reset();
        } catch (e) {
            window.alert('تعذر الاتصال بالخادم.');
        } finally {
            button.disabled = false;
        }
    }

    function bindForm(form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            const amount = form.querySelector('input[name="amount"]').value;
            addEntry(form.dataset.type, amount, form);
        });
    }

    bindForm(document.getElementById('cash-entry-form'));
    bindForm(document.getElementById('card-entry-form'));

    function openModal() {
        modalText.textContent = 'سيتم إغلاق الحساب اليوم وتوثيق ' +
            formatDisplay(state.cash) + ' د.ل نقدي، ' +
            formatDisplay(state.card) + ' د.ل بطاقة، ' +
            formatDisplay(state.transfers) + ' د.ل تحويلات، بإجمالي ' +
            formatDisplay(state.cash + state.card + state.transfers) +
            ' د.ل. هل أنت متأكد؟';
        modalError.classList.add('hidden');
        modalError.textContent = '';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function hideModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function printReceipt(closure) {
        const html = `<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إيصال إغلاق الحساب</title>
    <style>
        body { font-family: Tahoma, Arial, sans-serif; margin: 24px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 16px; text-align: center; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: right; }
        th { background: #f5f5f5; width: 40%; }
        .muted { margin-top: 16px; font-size: 11px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <h1>إيصال إغلاق الحساب</h1>
    <table>
        <tr><th>الموظف</th><td>${escapeHtml(closure.staff_name || '')}</td></tr>
        <tr><th>التاريخ والوقت</th><td>${escapeHtml(closure.closed_at || '')}</td></tr>
        <tr><th>نقدي</th><td>${formatAmount(closure.total_cash)} د.ل</td></tr>
        <tr><th>بطاقة</th><td>${formatAmount(closure.total_card)} د.ل</td></tr>
        <tr><th>تحويلات</th><td>${formatAmount(closure.total_transfers)} د.ل</td></tr>
        <tr><th>الإجمالي</th><td><strong>${formatAmount(closure.grand_total)} د.ل</strong></td></tr>
    </table>
    <p class="muted">سجل إغلاق موثّق في النظام</p>
</body>
</html>`;

        const printWindow = window.open('', '_blank', 'width=480,height=640');
        if (!printWindow) {
            window.alert('تم حفظ الإغلاق، لكن تعذر فتح نافذة الطباعة. يرجى السماح بالنوافذ المنبثقة.');
            return;
        }

        printWindow.document.open();
        printWindow.document.write(html);
        printWindow.document.close();
        printWindow.focus();

        const triggerPrint = function () {
            printWindow.print();
        };

        if (printWindow.document.readyState === 'complete') {
            triggerPrint();
        } else {
            printWindow.addEventListener('load', triggerPrint);
        }

        printWindow.addEventListener('afterprint', function () {
            printWindow.close();
        });
    }

    document.getElementById('close-account-btn').addEventListener('click', openModal);
    modal.querySelectorAll('[data-close-modal]').forEach(function (el) {
        el.addEventListener('click', hideModal);
    });

    confirmBtn.addEventListener('click', async function () {
        confirmBtn.disabled = true;
        modalError.classList.add('hidden');
        try {
            const response = await fetch(closeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({}),
            });

            if (!response.ok) {
                modalError.textContent = await parseError(response);
                modalError.classList.remove('hidden');
                return;
            }

            const data = await response.json();
            hideModal();
            printReceipt(data.closure || {});
            window.setTimeout(function () {
                window.location.reload();
            }, 400);
        } catch (e) {
            modalError.textContent = 'تعذر الاتصال بالخادم.';
            modalError.classList.remove('hidden');
        } finally {
            confirmBtn.disabled = false;
        }
    });

    updateSummary();
})();
</script>
@endpush
