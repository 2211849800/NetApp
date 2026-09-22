@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="bg-white rounded-2xl border border-purple-100 p-8 shadow-sm text-center">
    <h1 class="text-xl font-bold text-gray-800">{{ $title }}</h1>
    <p class="text-sm text-gray-500 mt-2">هذه الصفحة placeholder — سيتم تنفيذ الوظائف لاحقاً.</p>
</div>
@endsection
