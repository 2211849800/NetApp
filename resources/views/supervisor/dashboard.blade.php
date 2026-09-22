@extends('layouts.app')

@section('title', 'لوحة تحكم المشرف')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">مرحباً، {{ auth()->user()->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $roleLabel }} Dashboard</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-xl transition">
                تسجيل الخروج
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-purple-100 p-6 shadow-sm">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Role</p>
            <p class="text-lg font-semibold text-brand-700 mt-1">{{ $roleLabel }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-purple-100 p-6 shadow-sm">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Email</p>
            <p class="text-lg font-semibold text-gray-800 mt-1">{{ auth()->user()->email }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-purple-100 p-6 shadow-sm">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Status</p>
            <p class="text-lg font-semibold text-emerald-600 mt-1">{{ auth()->user()->status->label() }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-purple-100 p-6 shadow-sm">
        <h2 class="font-semibold text-gray-800 mb-2">Supervisor Dashboard</h2>
        <p class="text-sm text-gray-500">Placeholder — supervisory features will be implemented in upcoming phases.</p>
    </div>
</div>
@endsection
