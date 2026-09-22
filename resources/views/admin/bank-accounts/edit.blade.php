@extends('layouts.app')

@section('title', 'تعديل الحساب البنكي')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <x-admin.page-header title="تعديل الحساب البنكي: {{ $bankAccount->bank_name }}" />
    <x-admin.errors />
    <x-admin.form-card :back-url="route('admin.bank-accounts.index')">
        <form method="POST" action="{{ route('admin.bank-accounts.update', $bankAccount) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('admin.bank-accounts._form', ['bankAccount' => $bankAccount])
            <button type="submit" class="w-full py-3 bg-[#6F42C1] hover:bg-[#5B2BB8] text-white font-bold rounded-xl shadow-md transition">
                حفظ التعديلات
            </button>
        </form>
    </x-admin.form-card>
</div>
@endsection
