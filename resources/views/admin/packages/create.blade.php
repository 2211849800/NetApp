@extends('layouts.app')

@section('title', 'إضافة باقة')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <x-admin.page-header title="إضافة باقة جديدة" />
    <x-admin.form-card :back-url="route('admin.packages.index')">
        <form method="POST" action="{{ route('admin.packages.store') }}" class="space-y-4">
            @csrf
            @include('admin.packages._form')
            <button type="submit" class="w-full py-3 bg-[#6F42C1] text-white font-bold rounded-xl">حفظ</button>
        </form>
    </x-admin.form-card>
</div>
@endsection
