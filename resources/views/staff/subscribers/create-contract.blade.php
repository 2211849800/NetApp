@extends('layouts.app')

@section('title', 'إضافة عقد جديد')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" dir="rtl">
    <x-admin.page-header title="إضافة عقد جديد" subtitle="تسجيل عقد مشترك جديد وبيانات الخدمة" />
    <x-admin.flash />
    <x-admin.errors />

    <form method="POST" action="{{ route('staff.subscribers.contracts.store') }}" class="space-y-6">
        @csrf
        <section class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100"><h2 class="font-bold text-gray-900">معلومات الخدمة</h2><p class="text-xs text-gray-400 mt-1">بيانات الدخول والباقه المطلوبة</p></div>
            <div class="p-6 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <label class="text-xs font-semibold text-gray-700">رقم العقد<input id="contract-number" name="contract_number" value="{{ old('contract_number') }}" required autocomplete="off" class="mt-2 w-full px-4 py-3 rounded-xl border border-gray-200 font-mono"></label>
                <label class="text-xs font-semibold text-gray-700">كلمة مرور حساب البوابة<input name="password" type="password" required minlength="8" autocomplete="new-password" class="mt-2 w-full px-4 py-3 rounded-xl border border-gray-200"></label>
                <label class="text-xs font-semibold text-gray-700">تأكيد كلمة المرور<input name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="mt-2 w-full px-4 py-3 rounded-xl border border-gray-200"></label>
                <label class="text-xs font-semibold text-gray-700">الباقة <select name="package_id" required class="mt-2 w-full px-4 py-3 rounded-xl border border-gray-200"><option value="">اختر الباقة</option>@foreach($packages as $id => $package)<option value="{{ $id }}" @selected(old('package_id') === $id)>{{ $package['name'] }} — {{ number_format($package['price'], 0) }} د.ل</option>@endforeach</select></label>
                <label class="flex items-center gap-3 text-xs font-semibold text-gray-700"><input type="checkbox" name="details[automatic_renewal]" value="نعم" @checked(old('details.automatic_renewal')) class="rounded text-purple-600"> تجديد تلقائي</label>
                <label class="flex items-center gap-3 text-xs font-semibold text-gray-700"><input type="checkbox" name="details[borrowing_enabled]" value="نعم" @checked(old('details.borrowing_enabled')) class="rounded text-purple-600"> تفعيل خدمة السلفة</label>
            </div>
        </section>

        <section class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100"><h2 class="font-bold text-gray-900">بيانات الشبكة</h2><p class="text-xs text-gray-400 mt-1">تُجلب هذه القيم من النظام ومزوّد الشبكة.</p></div>
            <div class="p-6 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ([['عنوان IP', 'ip_address', '—'], ['IP جهاز المرسل', 'sender_device_ip', '—'], ['سرعة التحميل (Kbit/s)', 'download_speed', 'حسب الباقة'], ['سرعة الرفع (Kbit/s)', 'upload_speed', 'حسب الباقة'], ['عنوان MAC', 'mac_address', 'يُحدد تلقائياً'], ['DNS الأساسي', 'primary_dns', 'تلقائي'], ['DNS الثانوي', 'secondary_dns', 'تلقائي'], ['منفذ الاتصال بالجهاز', 'device_port', 'يُحدد تلقائياً']] as [$label, $key, $value])
                    <label class="text-xs font-semibold {{ $isAdmin ? 'text-gray-700' : 'text-gray-500' }}">{{ $label }}<input @if($isAdmin) name="details[{{ $key }}]" value="{{ old('details.'.$key) }}" @else value="{{ $value }}" disabled aria-readonly="true" @endif class="mt-2 w-full px-4 py-3 rounded-xl border border-gray-200 {{ $isAdmin ? 'bg-white' : 'bg-gray-100 text-gray-500 cursor-not-allowed' }}"></label>
                @endforeach
            </div>
        </section>

        <section class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100"><h2 class="font-bold text-gray-900">المعلومات الشخصية</h2></div>
            <div class="p-6 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <label class="text-xs font-semibold text-gray-700">الاسم الأول<input name="details[first_name]" value="{{ old('details.first_name') }}" required class="mt-2 w-full px-4 py-3 rounded-xl border border-gray-200"></label>
                <label class="text-xs font-semibold text-gray-700">اسم الأب<input name="details[father_name]" value="{{ old('details.father_name') }}" class="mt-2 w-full px-4 py-3 rounded-xl border border-gray-200"></label>
                <label class="text-xs font-semibold text-gray-700">اسم العائلة<input name="details[last_name]" value="{{ old('details.last_name') }}" required class="mt-2 w-full px-4 py-3 rounded-xl border border-gray-200"></label>
                <label class="text-xs font-semibold text-gray-700">الاسم الكامل<input name="name" value="{{ old('name') }}" required class="mt-2 w-full px-4 py-3 rounded-xl border border-gray-200"></label>
                <label class="text-xs font-semibold text-gray-700">رقم الهاتف<input name="phone" value="{{ old('phone') }}" required class="mt-2 w-full px-4 py-3 rounded-xl border border-gray-200" dir="ltr"></label>
                <label class="text-xs font-semibold text-gray-700">البريد الإلكتروني<input name="email" type="email" value="{{ old('email') }}" class="mt-2 w-full px-4 py-3 rounded-xl border border-gray-200" dir="ltr"></label>
                <label class="text-xs font-semibold text-gray-700">رقم الهوية<input name="national_id" value="{{ old('national_id') }}" class="mt-2 w-full px-4 py-3 rounded-xl border border-gray-200"></label>
                <label class="text-xs font-semibold text-gray-700">الجنسية<input name="details[nationality]" value="{{ old('details.nationality', 'ليبي') }}" class="mt-2 w-full px-4 py-3 rounded-xl border border-gray-200"></label>
                <label class="text-xs font-semibold text-gray-700">المدينة<input name="details[city]" value="{{ old('details.city') }}" class="mt-2 w-full px-4 py-3 rounded-xl border border-gray-200"></label>
                <label class="text-xs font-semibold text-gray-700">العنوان<input name="details[address]" value="{{ old('details.address') }}" class="mt-2 w-full px-4 py-3 rounded-xl border border-gray-200"></label>
                <label class="text-xs font-semibold text-gray-700">رقم جواز السفر<input name="details[passport_number]" value="{{ old('details.passport_number') }}" class="mt-2 w-full px-4 py-3 rounded-xl border border-gray-200"></label>
                <label class="text-xs font-semibold text-gray-700">طريقة السداد<input name="details[payment_method]" value="{{ old('details.payment_method') }}" class="mt-2 w-full px-4 py-3 rounded-xl border border-gray-200"></label>
                <label class="text-xs font-semibold text-gray-700 sm:col-span-2 lg:col-span-3">ملاحظات<textarea name="details[notes]" rows="3" class="mt-2 w-full px-4 py-3 rounded-xl border border-gray-200">{{ old('details.notes') }}</textarea></label>
            </div>
        </section>

        <section class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100"><h2 class="font-bold text-gray-900">خصائص متقدمة</h2></div>
            <div class="p-6 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ([
                    ['تاريخ بدء العقد', 'start_date', 'date', now()->toDateString()],
                    ['تاريخ انتهاء العقد', 'end_date', 'date', ''],
                    ['وقت التنزيل اليومي', 'download_start', 'time', ''],
                    ['وقت انتهاء التنزيل اليومي', 'download_end', 'time', ''],
                    ['حصة التنزيل', 'download_quota', 'number', ''],
                    ['حصة الرفع', 'upload_quota', 'number', ''],
                    ['حد السرعة بعد الحصة', 'throttle_speed', 'text', ''],
                ] as [$label, $key, $type, $default])
                    <label class="text-xs font-semibold {{ $isAdmin ? 'text-gray-700' : 'text-gray-500' }}">{{ $label }}<input type="{{ $type }}" @if($isAdmin) name="details[{{ $key }}]" value="{{ old('details.'.$key, $default) }}" @else value="{{ $default }}" disabled aria-readonly="true" @endif class="mt-2 w-full px-4 py-3 rounded-xl border border-gray-200 {{ $isAdmin ? 'bg-white' : 'bg-gray-100 text-gray-500 cursor-not-allowed' }}"></label>
                @endforeach
            </div>
        </section>

        <div class="flex justify-end gap-3"><a href="{{ route('staff.subscribers.index') }}" class="px-5 py-3 rounded-xl bg-gray-100 text-gray-700 text-sm font-bold">إلغاء</a><button class="px-6 py-3 rounded-xl bg-[#6F42C1] hover:bg-[#5B2BB8] text-white text-sm font-bold shadow-md">حفظ العقد</button></div>
    </form>
</div>
@endsection

