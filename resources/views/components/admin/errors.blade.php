@if ($errors->any())
    <div class="rounded-2xl bg-red-50 border border-red-100 px-4 py-3 text-sm text-red-700">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif
