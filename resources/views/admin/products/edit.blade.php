@extends('layouts.app')

@section('title', 'تعديل المنتج')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <x-admin.page-header title="تعديل المنتج: {{ $product->name }}" />
    <x-admin.errors />
    <x-admin.form-card :back-url="route('admin.products.index')">
        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            @include('admin.products._form', ['product' => $product])
            <button type="submit" class="w-full py-3 bg-[#6F42C1] hover:bg-[#5B2BB8] text-white font-bold rounded-xl shadow-md transition">
                حفظ التعديلات
            </button>
        </form>
    </x-admin.form-card>
</div>
@endsection
