@extends('layouts.app')

@section('title', 'إضافة حساب بنكي')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <x-admin.page-header title="إضافة حساب بنكي جديد" />
    <x-admin.errors />
    <x-admin.form-card :back-url="route('admin.bank-accounts.index')">
        <form method="POST" action="{{ route('admin.bank-accounts.store') }}" class="space-y-4">
            @csrf
            @include('admin.bank-accounts._form')
            <button type="submit" class="w-full py-3 bg-[#6F42C1] hover:bg-[#5B2BB8] text-white font-bold rounded-xl shadow-md transition">
                حفظ الحساب البنكي
            </button>
        </form>
    </x-admin.form-card>
</div>
@endsection
