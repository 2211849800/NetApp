@extends('layouts.app')

@section('title', 'باقات الإنترنت')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
        <div>
            <h2 class="text-xl font-black text-gray-900">باقات الإنترنت</h2>
            <p class="text-xs text-gray-400 mt-1">عرض وتعديل باقات الخدمة المتاحة للشركات والمشتركين</p>
        </div>
        <button class="px-4 py-2.5 bg-[#6F42C1] hover:bg-[#5B2BB8] text-white font-bold rounded-xl text-xs shadow-md transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>إضافة باقة جديدة</span>
        </button>
    </div>

    <!-- Packages Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($packages as $pkg)
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm hover:shadow-lg transition-all space-y-5 relative overflow-hidden group">
            <div class="absolute top-0 right-0 left-0 h-2 bg-gradient-to-r from-purple-600 to-indigo-500"></div>

            <div class="flex justify-between items-start pt-2">
                <div>
                    <h3 class="text-lg font-black text-gray-900">{{ $pkg['name'] }}</h3>
                    <span class="text-xs text-purple-600 font-bold">{{ $pkg['duration'] }}</span>
                </div>
                <div class="text-left">
                    <span class="text-xl font-black text-purple-700 font-mono">{{ $pkg['price'] }}</span>
                </div>
            </div>

            <div class="space-y-2 py-3 border-y border-gray-100 text-xs text-gray-600">
                <div class="flex justify-between">
                    <span>السرعة القصوى:</span>
                    <span class="font-bold text-gray-900 font-mono">{{ $pkg['speed'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span>الحصة الإجمالية:</span>
                    <span class="font-bold text-gray-900 font-mono">{{ $pkg['quota'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span>المشتركين النشطين:</span>
                    <span class="font-bold text-purple-700 font-mono">{{ number_format($pkg['active_subscribers']) }} مشترك</span>
                </div>
            </div>

            <div class="flex gap-2">
                <button class="flex-1 py-2 bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold rounded-xl text-xs transition">تعديل الباقة</button>
                <button class="px-3 py-2 bg-gray-50 hover:bg-gray-100 text-gray-600 font-bold rounded-xl text-xs transition">التفاصيل</button>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
