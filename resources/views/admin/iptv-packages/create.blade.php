@extends('layouts.app')

@section('title', 'إضافة باقة IPTV')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <x-admin.page-header title="إضافة باقة IPTV جديدة" />
    <x-admin.errors />
    <x-admin.form-card :back-url="route('admin.iptv-packages.index')">
        <form method="POST" action="{{ route('admin.iptv-packages.store') }}" class="space-y-4">
            @csrf
            @include('admin.iptv-packages._form')
            <button type="submit" class="w-full py-3 bg-[#6F42C1] hover:bg-[#5B2BB8] text-white font-bold rounded-xl shadow-md transition">
                إنشاء باقة IPTV
            </button>
        </form>
    </x-admin.form-card>
</div>
@endsection
