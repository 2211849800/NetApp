@extends('layouts.app')

@section('title', 'لوحة التحكم الرئيسيّة')

@section('content')
<!-- Welcome Header Section -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gradient-to-l from-purple-900/5 via-purple-500/5 to-transparent p-6 rounded-3xl border border-purple-100/60 shadow-2xs">
    <div>
        <h2 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2">
            <span>مرحباً صهيب!</span>
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </h2>
        <p class="text-sm font-medium text-gray-500 mt-1">إليك نظرة عامة على النظام والعمليات اليوم.</p>
    </div>
    
    <div class="flex items-center gap-3">
        <a href="{{ route('subscribers.index') }}" class="px-4 py-2.5 bg-white text-gray-700 hover:text-purple-700 border border-gray-200 rounded-xl text-xs font-bold shadow-2xs hover:bg-purple-50/50 transition-all flex items-center gap-2">
            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>إضافة مشترك جديد</span>
        </a>
        <a href="{{ route('mobile.demo') }}" class="px-4 py-2.5 bg-[#6F42C1] hover:bg-[#5B2BB8] text-white rounded-xl text-xs font-bold shadow-md shadow-purple-900/20 transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <span>عرض تطبيق المشترك</span>
        </a>
    </div>
</div>

<!-- 1. Top Stat Cards Grid (4 Columns) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
    <x-stat-card 
        title="إجمالي المشتركين" 
        value="{{ $stats['total_subscribers']['value'] }}" 
        change="{{ $stats['total_subscribers']['change'] }}" 
        period="{{ $stats['total_subscribers']['period'] }}"
        trend="up"
        icon="users" />

    <x-stat-card 
        title="المشتركين النشطين" 
        value="{{ $stats['active_subscribers']['value'] }}" 
        change="{{ $stats['active_subscribers']['change'] }}" 
        period="{{ $stats['active_subscribers']['period'] }}"
        trend="up"
        icon="user-check" />

    <x-stat-card 
        title="إجمالي الإيرادات" 
        value="{{ $stats['total_revenue']['value'] }}" 
        change="{{ $stats['total_revenue']['change'] }}" 
        period="{{ $stats['total_revenue']['period'] }}"
        trend="up"
        icon="wallet" />

    <x-stat-card 
        title="الشكاوى المفتوحة" 
        value="{{ $stats['open_complaints']['value'] }}" 
        change="{{ $stats['open_complaints']['change'] }}" 
        period="{{ $stats['open_complaints']['period'] }}"
        trend="down"
        icon="message-square" />
</div>

<!-- 2. Main Visual Content Grid: Charts (2/3) + Sidebar Cards (1/3) -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    <!-- Left/Center Column (8 cols): Charts & Main Tables -->
    <div class="lg:col-span-8 space-y-6">

        <!-- Chart Card 1: Subscribers Growth Line Chart -->
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="font-bold text-base text-gray-900">نمو المشتركين</h3>
                    <p class="text-xs text-gray-400">إحصائيات الاشتراك للأيام الماضية</p>
                </div>

                <!-- Dropdown Filter -->
                <div class="relative">
                    <select class="appearance-none bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-700 py-1.5 pr-3 pl-8 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500/20 cursor-pointer">
                        <option>آخر 7 أيام</option>
                        <option>آخر 30 يوم</option>
                        <option>هذا الشهر</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-2 text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- SVG Smooth Wave Line Chart (Matching Image 1 Chart) -->
            <div class="h-64 w-full relative">
                <!-- Y-Axis Labels -->
                <div class="absolute right-0 inset-y-0 flex flex-col justify-between text-[10px] text-gray-400 font-mono pr-1">
                    <span>40K</span>
                    <span>30K</span>
                    <span>20K</span>
                    <span>10K</span>
                    <span>0</span>
                </div>

                <!-- Chart Area with Gradient Fill -->
                <div class="mr-8 h-full flex flex-col justify-between">
                    <svg class="w-full h-48 overflow-visible" viewBox="0 0 500 150" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="purpleGradient" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#6F42C1" stop-opacity="0.3"/>
                                <stop offset="100%" stop-color="#6F42C1" stop-opacity="0.0"/>
                            </linearGradient>
                        </defs>
                        <!-- Grid Lines -->
                        <line x1="0" y1="0" x2="500" y2="0" stroke="#F3F4F6" stroke-dasharray="4"/>
                        <line x1="0" y1="37" x2="500" y2="37" stroke="#F3F4F6" stroke-dasharray="4"/>
                        <line x1="0" y1="75" x2="500" y2="75" stroke="#F3F4F6" stroke-dasharray="4"/>
                        <line x1="0" y1="112" x2="500" y2="112" stroke="#F3F4F6" stroke-dasharray="4"/>
                        <line x1="0" y1="150" x2="500" y2="150" stroke="#E5E7EB"/>

                        <!-- Area Fill -->
                        <path d="M 0 120 Q 80 80, 160 100 T 320 60 T 500 20 L 500 150 L 0 150 Z" fill="url(#purpleGradient)" />
                        <!-- Smooth Curve Line -->
                        <path d="M 0 120 Q 80 80, 160 100 T 320 60 T 500 20" fill="none" stroke="#6F42C1" stroke-width="3" stroke-linecap="round"/>

                        <!-- Data Points Circles -->
                        <circle cx="0" cy="120" r="4" fill="#6F42C1" stroke="#FFFFFF" stroke-width="2"/>
                        <circle cx="80" cy="85" r="4" fill="#6F42C1" stroke="#FFFFFF" stroke-width="2"/>
                        <circle cx="160" cy="100" r="4" fill="#6F42C1" stroke="#FFFFFF" stroke-width="2"/>
                        <circle cx="240" cy="85" r="4" fill="#6F42C1" stroke="#FFFFFF" stroke-width="2"/>
                        <circle cx="320" cy="60" r="4" fill="#6F42C1" stroke="#FFFFFF" stroke-width="2"/>
                        <circle cx="400" cy="95" r="4" fill="#6F42C1" stroke="#FFFFFF" stroke-width="2"/>
                        <circle cx="500" cy="20" r="5" fill="#5B2BB8" stroke="#FFFFFF" stroke-width="2.5"/>
                    </svg>

                    <!-- X-Axis Labels -->
                    <div class="flex justify-between text-[11px] text-gray-400 pt-2 font-medium">
                        <span>مايو 09</span>
                        <span>مايو 10</span>
                        <span>مايو 11</span>
                        <span>مايو 12</span>
                        <span>مايو 13</span>
                        <span>مايو 14</span>
                        <span>مايو 15</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Card 2: Donut Chart - Subscribers by Package -->
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-base text-gray-900">المشتركين حسب الباقة</h3>
                <span class="text-xs text-purple-600 font-semibold cursor-pointer hover:underline">التفاصيل</span>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-around gap-6 py-4">
                <!-- SVG Donut Chart -->
                <div class="relative w-40 h-40 flex items-center justify-center shrink-0">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                        <!-- Background Circle -->
                        <path class="text-purple-50" stroke-width="4" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        
                        <!-- Segment 1: Mega 70 (43%) -->
                        <path class="text-[#5B2BB8]" stroke-dasharray="43, 100" stroke-width="4.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        
                        <!-- Segment 2: Mega 150 (27%) -->
                        <path class="text-[#7C3AED]" stroke-dasharray="27, 100" stroke-dashoffset="-43" stroke-width="4.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />

                        <!-- Segment 3: Mega 300 (18%) -->
                        <path class="text-[#A78BFA]" stroke-dasharray="18, 100" stroke-dashoffset="-70" stroke-width="4.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />

                        <!-- Segment 4: Other (12%) -->
                        <path class="text-purple-200" stroke-dasharray="12, 100" stroke-dashoffset="-88" stroke-width="4.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>

                    <!-- Center Label -->
                    <div class="absolute text-center">
                        <span class="block text-xl font-black text-gray-900">100%</span>
                        <span class="block text-[10px] text-gray-400">التوزيع</span>
                    </div>
                </div>

                <!-- Legend List -->
                <div class="grid grid-cols-2 sm:grid-cols-1 gap-3 w-full max-w-xs">
                    <div class="flex items-center justify-between p-2 rounded-xl bg-gray-50/70 border border-gray-100">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-[#5B2BB8]"></span>
                            <span class="text-xs font-bold text-gray-800">ميقا 70</span>
                        </div>
                        <span class="text-xs font-mono font-bold text-gray-600">43%</span>
                    </div>

                    <div class="flex items-center justify-between p-2 rounded-xl bg-gray-50/70 border border-gray-100">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-[#7C3AED]"></span>
                            <span class="text-xs font-bold text-gray-800">ميقا 150</span>
                        </div>
                        <span class="text-xs font-mono font-bold text-gray-600">27%</span>
                    </div>

                    <div class="flex items-center justify-between p-2 rounded-xl bg-gray-50/70 border border-gray-100">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-[#A78BFA]"></span>
                            <span class="text-xs font-bold text-gray-800">ميقا 300</span>
                        </div>
                        <span class="text-xs font-mono font-bold text-gray-600">18%</span>
                    </div>

                    <div class="flex items-center justify-between p-2 rounded-xl bg-gray-50/70 border border-gray-100">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-purple-200"></span>
                            <span class="text-xs font-bold text-gray-800">أخرى</span>
                        </div>
                        <span class="text-xs font-mono font-bold text-gray-600">12%</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Right Side Column (4 cols): Status Cards, Alerts & Shortcuts -->
    <div class="lg:col-span-4 space-y-6">

        <!-- Card 1: Complaints by Status -->
        <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-sm text-gray-900">الشكاوى حسب الحالة</h3>
                <a href="{{ route('complaints.index') }}" class="text-xs text-purple-600 font-semibold hover:underline">عرض الكل</a>
            </div>

            <div class="space-y-2.5">
                <div class="flex items-center justify-between p-2.5 rounded-2xl bg-rose-50/40 border border-rose-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-gray-800">مفتوحة</span>
                    </div>
                    <span class="text-sm font-black text-gray-900 font-mono">32</span>
                </div>

                <div class="flex items-center justify-between p-2.5 rounded-2xl bg-amber-50/40 border border-amber-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-gray-800">قيد المعالجة</span>
                    </div>
                    <span class="text-sm font-black text-gray-900 font-mono">18</span>
                </div>

                <div class="flex items-center justify-between p-2.5 rounded-2xl bg-blue-50/40 border border-blue-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-gray-800">بانتظار العملاء</span>
                    </div>
                    <span class="text-sm font-black text-gray-900 font-mono">7</span>
                </div>

                <div class="flex items-center justify-between p-2.5 rounded-2xl bg-emerald-50/40 border border-emerald-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-gray-800">تم الحل</span>
                    </div>
                    <span class="text-sm font-black text-gray-900 font-mono">45</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Alerts Box -->
        <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm space-y-3">
            <h3 class="font-bold text-sm text-gray-900 border-b border-gray-100 pb-2">تنبيهات</h3>

            <div class="space-y-2.5">
                <div class="p-3 rounded-2xl bg-amber-50/60 border border-amber-200/50 flex items-center justify-between group hover:bg-amber-100/60 transition cursor-pointer">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span class="text-xs font-bold text-amber-900">5 باقات ستنتهي خلال 3 أيام</span>
                    </div>
                    <span class="text-[10px] text-amber-700 font-bold group-hover:underline">عرض التفاصيل &rsaquo;</span>
                </div>

                <div class="p-3 rounded-2xl bg-rose-50/60 border border-rose-200/50 flex items-center justify-between group hover:bg-rose-100/60 transition cursor-pointer">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        <span class="text-xs font-bold text-rose-900">فشل 3 مدفوعات</span>
                    </div>
                    <span class="text-[10px] text-rose-700 font-bold group-hover:underline">عرض التفاصيل &rsaquo;</span>
                </div>

                <div class="p-3 rounded-2xl bg-purple-50/60 border border-purple-200/50 flex items-center justify-between group hover:bg-purple-100/60 transition cursor-pointer">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                        <span class="text-xs font-bold text-purple-900">جهاز Offline منذ أكثر من 24 ساعة</span>
                    </div>
                    <span class="text-[10px] text-purple-700 font-bold group-hover:underline">عرض التفاصيل &rsaquo;</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Quick Shortcuts Grid -->
        <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm space-y-3">
            <h3 class="font-bold text-sm text-gray-900 border-b border-gray-100 pb-2">اختصارات سريعة</h3>

            <div class="grid grid-cols-3 gap-2.5">
                <a href="{{ route('complaints.index') }}" class="flex flex-col items-center justify-center p-3 rounded-2xl bg-gray-50/80 border border-gray-100 hover:bg-purple-50 hover:border-purple-200 text-gray-700 hover:text-purple-700 transition text-center group">
                    <div class="w-8 h-8 rounded-xl bg-white shadow-2xs flex items-center justify-center text-purple-600 mb-1.5 group-hover:scale-110 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <span class="text-[11px] font-bold">تقديم شكوى</span>
                </a>

                <a href="{{ route('packages.index') }}" class="flex flex-col items-center justify-center p-3 rounded-2xl bg-gray-50/80 border border-gray-100 hover:bg-purple-50 hover:border-purple-200 text-gray-700 hover:text-purple-700 transition text-center group">
                    <div class="w-8 h-8 rounded-xl bg-white shadow-2xs flex items-center justify-center text-purple-600 mb-1.5 group-hover:scale-110 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="text-[11px] font-bold">شحن باقة</span>
                </a>

                <a href="{{ route('subscribers.index') }}" class="flex flex-col items-center justify-center p-3 rounded-2xl bg-gray-50/80 border border-gray-100 hover:bg-purple-50 hover:border-purple-200 text-gray-700 hover:text-purple-700 transition text-center group">
                    <div class="w-8 h-8 rounded-xl bg-white shadow-2xs flex items-center justify-center text-purple-600 mb-1.5 group-hover:scale-110 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    <span class="text-[11px] font-bold">إضافة مشترك</span>
                </a>

                <a href="#" class="flex flex-col items-center justify-center p-3 rounded-2xl bg-gray-50/80 border border-gray-100 hover:bg-purple-50 hover:border-purple-200 text-gray-700 hover:text-purple-700 transition text-center group">
                    <div class="w-8 h-8 rounded-xl bg-white shadow-2xs flex items-center justify-center text-purple-600 mb-1.5 group-hover:scale-110 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <span class="text-[11px] font-bold">الإشعارات</span>
                </a>

                <a href="{{ route('subscribers.index') }}" class="flex flex-col items-center justify-center p-3 rounded-2xl bg-gray-50/80 border border-gray-100 hover:bg-purple-50 hover:border-purple-200 text-gray-700 hover:text-purple-700 transition text-center group">
                    <div class="w-8 h-8 rounded-xl bg-white shadow-2xs flex items-center justify-center text-purple-600 mb-1.5 group-hover:scale-110 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <span class="text-[11px] font-bold">المشتركين</span>
                </a>

                <a href="{{ route('payments.index') }}" class="flex flex-col items-center justify-center p-3 rounded-2xl bg-gray-50/80 border border-gray-100 hover:bg-purple-50 hover:border-purple-200 text-gray-700 hover:text-purple-700 transition text-center group">
                    <div class="w-8 h-8 rounded-xl bg-white shadow-2xs flex items-center justify-center text-purple-600 mb-1.5 group-hover:scale-110 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <span class="text-[11px] font-bold">تقرير الإيرادات</span>
                </a>
            </div>
        </div>

    </div>

</div>

<!-- 3. Bottom Table Section: Latest Operations -->
<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
    <!-- Header -->
    <div class="p-6 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="font-bold text-base text-gray-900">آخر العمليات</h3>
            <p class="text-xs text-gray-400">سجل أحدث العمليات الشحن والاشتراكات والشكاوى</p>
        </div>
        <a href="#" class="text-xs text-purple-600 font-bold hover:underline">عرض الكل</a>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-right text-xs">
            <thead>
                <tr class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-bold">
                    <th class="py-3.5 px-6">العملية</th>
                    <th class="py-3.5 px-6">المشترك</th>
                    <th class="py-3.5 px-6">نوع العملية</th>
                    <th class="py-3.5 px-6">الموظف</th>
                    <th class="py-3.5 px-6">التاريخ</th>
                    <th class="py-3.5 px-6">الحالة</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
                @foreach($latestOperations as $op)
                <tr class="hover:bg-purple-50/20 transition-colors">
                    <td class="py-4 px-6 font-bold text-gray-900">{{ $op['operation'] }}</td>
                    <td class="py-4 px-6">
                        <div class="font-bold text-gray-900">{{ $op['subscriber'] }}</div>
                        <div class="text-[10px] text-gray-400 font-mono">#{{ $op['contract_no'] }}</div>
                    </td>
                    <td class="py-4 px-6 font-medium text-gray-600">{{ $op['type'] }}</td>
                    <td class="py-4 px-6 font-medium text-gray-600">{{ $op['employee'] }}</td>
                    <td class="py-4 px-6 text-gray-400 font-mono text-[11px]">{{ $op['date'] }}</td>
                    <td class="py-4 px-6">
                        <x-status-badge :status="$op['status']" :label="$op['status_label']" />
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Table Footer Link -->
    <div class="p-4 bg-gray-50/50 border-t border-gray-100 text-center">
        <a href="#" class="text-xs font-bold text-purple-700 hover:text-purple-900 inline-flex items-center gap-1 transition">
            <span>عرض كل العمليات</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </a>
    </div>
</div>
@endsection
