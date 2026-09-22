@if (session('status'))
    <div class="rounded-2xl bg-emerald-50 border border-emerald-100 px-5 py-4 text-sm text-emerald-800">
        {{ session('status') }}
    </div>
@endif

@if (session('error'))
    <div class="rounded-2xl bg-red-50 border border-red-100 px-5 py-4 text-sm text-red-800">
        {{ session('error') }}
    </div>
@endif
