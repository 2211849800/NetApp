@extends('layouts.app')

@section('title', 'تطبيق الهواتف الذكية - للمأخوذ من تصميم ميقا الجديدة')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-[#2C1D54] to-[#6F42C1] rounded-3xl p-6 text-white shadow-lg flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/20 rounded-full text-xs font-semibold mb-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                تطبيق المشتركين
            </span>
            <h2 class="text-2xl font-black">واجهة تطبيق الهواتف الذكية (Subscriber Mobile App)</h2>
            <p class="text-xs text-purple-200 mt-1">معاينة تفاعلية لشاشات تطبيق الهاتف من الهوية البصرية الرسمية (Image 2)</p>
        </div>
        <a href="{{ route('dashboard.redirect') }}" class="px-5 py-2.5 bg-white text-[#2C1D54] font-bold rounded-xl text-xs hover:bg-purple-50 transition shadow-md shrink-0">
            العودة للوحة التحكم
        </a>
    </div>

    <!-- Mobile Frame Demonstration Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 justify-items-center">

        <!-- Mobile Screen 1: Home Dashboard -->
        <div class="w-full max-w-sm bg-white rounded-[40px] border-[8px] border-gray-900 shadow-2xl overflow-hidden relative font-sans flex flex-col h-[700px]">
            <!-- Top Phone Status Bar -->
            <div class="bg-[#2C1D54] px-6 pt-3 pb-2 text-white flex justify-between items-center text-xs font-mono">
                <span>9:41</span>
                <div class="w-16 h-3 bg-black/40 rounded-full"></div>
                <span class="inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0"/></svg>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6a1 1 0 011 1v8a1 1 0 01-1 1H9a1 1 0 01-1-1V8a1 1 0 011-1zm9 3v4"/></svg>
                </span>
            </div>

            <!-- App Top Header -->
            <div class="bg-[#2C1D54] p-5 text-white flex justify-between items-center">
                <button class="p-1.5 rounded-full bg-white/10 text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg></button>
                <div class="text-center">
                    <span class="font-bold text-lg text-white">M</span>
                </div>
                <div class="w-8 h-8 rounded-full bg-purple-400 text-purple-950 font-bold flex items-center justify-center text-xs">ص</div>
            </div>

            <!-- Scrollable Screen Content -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50/60 custom-scrollbar">
                <!-- User Greeting Card -->
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-sm text-gray-900">مرحباً صهيب</h3>
                        <p class="text-[11px] text-gray-400">رقم المشترك: <span class="font-mono text-purple-700 font-bold">123456</span></p>
                    </div>
                </div>

                <!-- Package Usage Card (Purple Gradient Donut) -->
                <div class="bg-gradient-to-br from-[#6F42C1] to-[#5B2BB8] text-white rounded-3xl p-5 shadow-lg space-y-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-[10px] text-purple-200 block">الباقة الحالية</span>
                            <h4 class="text-lg font-black">ميقا 70</h4>
                            <p class="text-[10px] text-purple-200">المتبقي: <span class="font-bold text-white">49 GB</span> من 79 GB</p>
                        </div>
                        
                        <!-- Usage Circular Progress Ring -->
                        <div class="w-16 h-16 rounded-full border-4 border-white/20 border-t-white flex items-center justify-center font-bold text-xs">
                            70%
                        </div>
                    </div>

                    <div class="pt-3 border-t border-white/20 flex justify-between items-center text-[11px]">
                        <div>
                            <span class="text-purple-200 block">تاريخ الانتهاء</span>
                            <span class="font-bold font-mono">2025 / 07 / 25</span>
                        </div>
                        <div class="text-left">
                            <span class="text-purple-200 block">الأيام المتبقية</span>
                            <span class="font-bold">3 أيام</span>
                        </div>
                    </div>

                    <button class="w-full py-2.5 bg-white text-[#5B2BB8] font-black rounded-xl text-xs shadow-md hover:bg-purple-50 transition">
                        شحن الباقة الآن
                    </button>
                </div>

                <!-- Quick Services -->
                <div>
                    <h4 class="font-bold text-xs text-gray-800 mb-2">الخدمات السريعة</h4>
                    <div class="grid grid-cols-4 gap-2 text-center">
                        <div class="bg-white p-2.5 rounded-2xl border border-gray-100 shadow-2xs flex flex-col items-center">
                            <svg class="w-5 h-5 mb-1 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            <span class="text-[9px] font-bold text-gray-700">الدعم عبر واتساب</span>
                        </div>
                        <div class="bg-white p-2.5 rounded-2xl border border-gray-100 shadow-2xs flex flex-col items-center">
                            <svg class="w-5 h-5 mb-1 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span class="text-[9px] font-bold text-gray-700">طلب اشتراك جديد</span>
                        </div>
                        <div class="bg-white p-2.5 rounded-2xl border border-gray-100 shadow-2xs flex flex-col items-center">
                            <svg class="w-5 h-5 mb-1 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="text-[9px] font-bold text-gray-700">تقديم شكوى</span>
                        </div>
                        <div class="bg-white p-2.5 rounded-2xl border border-gray-100 shadow-2xs flex flex-col items-center">
                            <svg class="w-5 h-5 mb-1 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg>
                            <span class="text-[9px] font-bold text-gray-700">شحن الباقة</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Navigation Bar -->
            <div class="bg-white border-t border-gray-100 px-3 py-2 flex justify-around items-center text-[10px] text-gray-400 font-medium">
                <div class="flex flex-col items-center text-[#5B2BB8] font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>الرئيسية</span>
                </div>
                <div class="flex flex-col items-center hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span>الإشعارات</span>
                </div>
                <div class="flex flex-col items-center hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <span>الشكاوى</span>
                </div>
                <div class="flex flex-col items-center hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span>باقاتي</span>
                </div>
                <div class="flex flex-col items-center hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>الحساب</span>
                </div>
            </div>
        </div>

        <!-- Mobile Screen 2: Package Recharge Flow -->
        <div class="w-full max-w-sm bg-white rounded-[40px] border-[8px] border-gray-900 shadow-2xl overflow-hidden relative font-sans flex flex-col h-[700px]">
            <!-- Top Status -->
            <div class="bg-white px-6 pt-3 pb-2 text-gray-800 flex justify-between items-center text-xs font-mono border-b border-gray-100">
                <span>9:41</span>
                <span class="font-bold text-gray-900 text-sm">شحن الباقة</span>
                <span class="inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0"/></svg>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6a1 1 0 011 1v8a1 1 0 01-1 1H9a1 1 0 01-1-1V8a1 1 0 011-1zm9 3v4"/></svg>
                </span>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50/60 custom-scrollbar">
                <!-- Select Package section -->
                <div class="space-y-2">
                    <h4 class="font-bold text-xs text-gray-800">اختر الباقة</h4>

                    <div class="p-3 bg-white border border-gray-200 rounded-2xl flex items-center justify-between cursor-pointer hover:border-purple-500">
                        <div>
                            <h5 class="font-bold text-xs text-gray-900">ميقا 30</h5>
                            <span class="text-[10px] text-gray-400">30 يوم / 30 GB</span>
                        </div>
                        <span class="font-bold text-xs text-purple-700 font-mono">30 د.ل</span>
                    </div>

                    <div class="p-3 bg-purple-50/80 border-2 border-purple-600 rounded-2xl flex items-center justify-between cursor-pointer">
                        <div>
                            <h5 class="font-bold text-xs text-purple-900">ميقا 70</h5>
                            <span class="text-[10px] text-purple-600">30 يوم / 79 GB</span>
                        </div>
                        <span class="font-bold text-xs text-purple-700 font-mono">70 د.ل</span>
                    </div>

                    <div class="p-3 bg-white border border-gray-200 rounded-2xl flex items-center justify-between cursor-pointer hover:border-purple-500">
                        <div>
                            <h5 class="font-bold text-xs text-gray-900">ميقا 150</h5>
                            <span class="text-[10px] text-gray-400">30 يوم / 150 GB</span>
                        </div>
                        <span class="font-bold text-xs text-purple-700 font-mono">150 د.ل</span>
                    </div>
                </div>

                <!-- Payment Method Section -->
                <div class="space-y-2 pt-2">
                    <h4 class="font-bold text-xs text-gray-800">اختر طريقة الدفع</h4>

                    <div class="space-y-2">
                        <label class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-2xl cursor-pointer hover:border-purple-500">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg>
                                <span class="text-xs font-bold text-gray-800">Lypay</span>
                            </div>
                            <input type="radio" name="gateway" class="text-purple-600 focus:ring-purple-500"/>
                        </label>

                        <label class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-2xl cursor-pointer hover:border-purple-500">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span class="text-xs font-bold text-gray-800">OnePay</span>
                            </div>
                            <input type="radio" name="gateway" class="text-purple-600 focus:ring-purple-500"/>
                        </label>

                        <label class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-2xl cursor-pointer hover:border-purple-500">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                                <span class="text-xs font-bold text-gray-800">تحويل بنكي</span>
                            </div>
                            <input type="radio" name="gateway" class="text-purple-600 focus:ring-purple-500"/>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Footer Action Button -->
            <div class="p-4 bg-white border-t border-gray-100">
                <button class="w-full py-3 bg-[#5B2BB8] text-white font-bold rounded-2xl text-xs shadow-md">
                    متابعة الدفع (70 د.ل)
                </button>
            </div>
        </div>

        <!-- Mobile Screen 3: Technical Support Chat -->
        <div class="w-full max-w-sm bg-white rounded-[40px] border-[8px] border-gray-900 shadow-2xl overflow-hidden relative font-sans flex flex-col h-[700px]">
            <!-- Header -->
            <div class="bg-[#2C1D54] p-4 text-white flex justify-between items-center">
                <span class="text-xs font-bold">الدعم الفني</span>
                <span class="inline-flex items-center gap-1 text-[10px] text-emerald-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    متصل الآن
                </span>
            </div>

            <!-- Chat messages container -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50/80 custom-scrollbar text-xs">
                <!-- Support Message -->
                <div class="bg-white p-3 rounded-2xl rounded-tr-none border border-gray-100 shadow-2xs max-w-[80%] space-y-1">
                    <p class="text-gray-800">مرحباً صهيب! كيف يمكننا مساعدتك اليوم؟</p>
                    <span class="text-[9px] text-gray-400 block text-left">10:30 AM</span>
                </div>

                <!-- User Message -->
                <div class="bg-[#5B2BB8] text-white p-3 rounded-2xl rounded-tl-none shadow-2xs max-w-[80%] mr-auto space-y-1">
                    <p>عندي مشكلة في السرعة اليوم.</p>
                    <span class="text-[9px] text-purple-200 block text-left">10:31 AM</span>
                </div>

                <!-- Support Ticket Created Card -->
                <div class="bg-purple-50 border border-purple-200 p-3 rounded-2xl text-center space-y-1">
                    <span class="text-[10px] text-purple-700 font-bold block">تم إنشاء تذكرة برقم</span>
                    <span class="font-mono font-black text-purple-900 text-sm">#TK-2025-0158</span>
                    <p class="text-[10px] text-purple-600">وسيتواصل معك فريقنا الفني قريباً.</p>
                </div>
            </div>

            <!-- Chat Input bar -->
            <div class="p-3 bg-white border-t border-gray-100 flex items-center gap-2">
                <input type="text" placeholder="اكتب رسالتك..." class="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none"/>
                <button class="w-8 h-8 rounded-xl bg-[#5B2BB8] text-white flex items-center justify-center shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </div>
        </div>

    </div>
</div>
@endsection
