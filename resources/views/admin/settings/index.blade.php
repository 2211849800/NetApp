@extends('layouts.app')

@section('title', 'إعدادات النظام')

@section('content')
<div class="space-y-6 max-w-4xl">
    <x-admin.page-header title="إعدادات النظام العامة" subtitle="التحكم في مزود الخدمات ومحدادات السلفة والعملة وإعدادات المحاكاة" />
    <x-admin.flash />

    <form action="{{ route('admin.settings.update') }}" method="POST" class="bg-white rounded-3xl border border-gray-100 p-6 sm:p-8 space-y-6 shadow-sm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-b pb-6">
            <!-- Provider Toggle -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-2">مزود بيانات المشتركين (Subscriber Provider)</label>
                <select name="subscriber_provider" class="w-full px-4 py-2.5 rounded-xl border text-xs bg-gray-50 focus:bg-white font-mono">
                    <option value="mock" @selected($settings['subscriber_provider'] === 'mock')>Mock Provider (محاكي التراسل المحلي)</option>
                    <option value="adv" @selected($settings['subscriber_provider'] === 'adv')>ADV Radius API (السيرفر الخارجي الحقيقي)</option>
                </select>
                <p class="text-[11px] text-gray-400 mt-1">يحدد مصدر استعلام وشحن باقات المشتركين.</p>
            </div>

            <!-- Payment Mock Mode -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-2">وضع تجريب بوابات الدفع (Payment Mock Mode)</label>
                <select name="payment_mock_mode" class="w-full px-4 py-2.5 rounded-xl border text-xs bg-gray-50 focus:bg-white font-mono">
                    <option value="true" @selected($settings['payment_mock_mode'] === 'true')>مفعّل (السماح بالمحاكاة والتجارب بدون مفاتيح حقيقية)</option>
                    <option value="false" @selected($settings['payment_mock_mode'] === 'false')>معطّل (بيئة الإنتاج الحقيقية فقط)</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-b pb-6">
            <!-- Emergency Borrowing GB -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-2">حصة سلفة الطوارئ (GB)</label>
                <input type="number" name="emergency_borrowing_gb" value="{{ $settings['emergency_borrowing_gb'] }}" required class="w-full px-4 py-2.5 rounded-xl border text-xs font-mono">
            </div>

            <!-- Emergency Borrowing Days -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-2">مهلة سلفة الطوارئ (أيام)</label>
                <input type="number" name="emergency_borrowing_days" value="{{ $settings['emergency_borrowing_days'] }}" required class="w-full px-4 py-2.5 rounded-xl border text-xs font-mono">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Currency Symbol -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-2">رمز العملة الرسمية</label>
                <input type="text" name="currency_symbol" value="{{ $settings['currency_symbol'] }}" required class="w-full px-4 py-2.5 rounded-xl border text-xs">
            </div>

            <!-- Support Phone -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-2">رقم الدعم الفني للمشتركين</label>
                <input type="text" name="support_phone" value="{{ $settings['support_phone'] }}" required class="w-full px-4 py-2.5 rounded-xl border text-xs font-mono">
            </div>
        </div>

        <div class="flex items-center justify-end pt-4 border-t">
            <button type="submit" class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                حفظ التغييرات وإعدادات النظام
            </button>
        </div>
    </form>
</div>
@endsection
