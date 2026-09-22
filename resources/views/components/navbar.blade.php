<header class="bg-white border-b border-gray-100 sticky top-0 z-40 px-6 py-3 shadow-sm flex items-center justify-between">
    <div class="flex items-center gap-4 flex-1 max-w-xl">
        <button id="sidebarToggle" class="lg:hidden p-2 text-gray-500 hover:text-purple-700 hover:bg-purple-50 rounded-xl transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <div class="relative w-full hidden sm:block">
            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text"
                   placeholder="ابحث عن مشترك، رقم عقد، هاتف..."
                   class="w-full pr-10 pl-4 py-2 bg-gray-50/80 border border-gray-200/80 rounded-xl text-xs text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all"/>
        </div>
    </div>

    <div class="flex items-center gap-4">
        @auth
        <a href="{{ route('admin.profile.index') }}" class="flex items-center gap-2 text-gray-700 hover:text-purple-700 bg-gray-50 hover:bg-purple-50 px-3 py-1.5 rounded-xl border border-gray-100 transition">
            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span class="text-xs font-semibold">{{ auth()->user()->username ?? auth()->user()->name }}</span>
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 rounded-xl transition">
                خروج
            </button>
        </form>
        @endauth
    </div>
</header>
