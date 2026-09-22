@extends('layouts.app')

@section('title', 'تعديل الباقة')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <x-admin.page-header title="تعديل الباقة: {{ $package->name }}" />
    <x-admin.errors />
    <x-admin.form-card :back-url="route('admin.packages.index')">
        <form method="POST" action="{{ route('admin.packages.update', $package) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('admin.packages._form', ['package' => $package])
            <button type="submit" class="w-full py-3 bg-[#6F42C1] hover:bg-[#5B2BB8] text-white font-bold rounded-xl shadow-md transition">
                حفظ التعديلات
            </button>
        </form>
    </x-admin.form-card>
</div>
@endsection
