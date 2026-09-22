@php
    $user = auth()->user();
    $user?->loadMissing('roles.permissions');

    $navItems = collect(config('navigation.items', []))->filter(function ($item) use ($user) {
        if (! $user) {
            return false;
        }

        if (isset($item['roles']) && ! $user->hasAnyRole($item['roles'])) {
            return false;
        }

        return $user->hasPermission($item['permission']);
    });
@endphp

<aside class="fixed inset-y-0 right-0 z-50 w-64 bg-[#2C1D54] text-white flex flex-col justify-between transition-transform duration-300 transform lg:translate-x-0 shadow-2xl">
    <!-- Top Brand Header -->
    <div class="p-5 flex flex-col items-center border-b border-purple-900/50">
        <div class="flex items-center gap-3 w-full">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-400 flex items-center justify-center shadow-lg shadow-purple-900/40 shrink-0">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div class="text-right overflow-hidden">
                <h1 class="font-bold text-base leading-tight text-white tracking-wide">ميقا الجديدة</h1>
                <p class="text-[10px] text-purple-300 font-light truncate">للاتصالات والتقنية</p>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <div class="flex-1 overflow-y-auto px-3 py-4 space-y-1.5 custom-scrollbar">
        @foreach ($navItems as $item)
            @php
                $routeName = $item['route'];
                $isActive = request()->routeIs($routeName) || request()->routeIs(str_replace('.index', '.*', $routeName));
            @endphp
            <a href="{{ Route::has($routeName) ? route($routeName) : '#' }}"
               title="{{ $item['label'] }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ $isActive ? 'bg-[#6F42C1] text-white shadow-lg shadow-purple-900/50 font-semibold' : 'text-purple-200 hover:bg-purple-900/40 hover:text-white' }}">
                <x-nav-icon :name="$item['icon'] ?? 'menu'" class="w-5 h-5 shrink-0 {{ $isActive ? 'text-white' : 'text-purple-300' }}" />
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach

        @if ($user && $user->isStaff())
        <div class="pt-2">
            <a href="{{ route('staff.my-balance.index') }}"
               title="رصيدي الحسابي"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-bold text-xs bg-emerald-600/90 text-white shadow-md hover:bg-emerald-600 transition-all">
                <x-nav-icon name="wallet" class="w-5 h-5 shrink-0 text-emerald-100" />
                <span>رصيدي الحسابي</span>
            </a>
        </div>
        @endif

        @can('view_dashboard')
        <div class="pt-2">
            <a href="{{ route('mobile.demo') }}"
               title="معاينة تطبيق الهاتف"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-xs bg-gradient-to-r from-purple-500 to-indigo-500 text-white shadow-md hover:opacity-95 transition-all">
                <x-nav-icon name="mobile" class="w-5 h-5 shrink-0 text-purple-100" />
                <span>معاينة تطبيق الهاتف</span>
            </a>
        </div>
        @endcan

    </div>

    <!-- User Profile Footer -->
    @auth
    <div class="p-3.5 bg-[#211442] border-t border-purple-900/40">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-purple-400 flex items-center justify-center text-purple-950 font-bold text-sm shadow-md shrink-0">
                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="text-right min-w-0">
                    <h4 class="font-bold text-xs text-white leading-tight truncate">{{ auth()->user()->name }}</h4>
                    <p class="text-[10px] text-purple-300 truncate">
                        {{ auth()->user()->roles->first()?->display_name ?? 'Staff' }}
                    </p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-purple-300 hover:text-white p-1.5 rounded-lg hover:bg-purple-800/40 transition" title="تسجيل الخروج">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
    @endauth
</aside>
