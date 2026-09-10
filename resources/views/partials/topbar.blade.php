<header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-night-700 bg-night-900 px-4 sm:px-6">
    <div class="flex items-center gap-3">
        <button type="button" class="rounded-lg p-2 text-stone-300 transition hover:bg-night-800 lg:hidden" aria-label="Buka menu navigasi" @click="sidebarOpen = true">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <h1 class="text-lg font-semibold text-stone-100">@yield('header', 'Dashboard')</h1>
    </div>

    <div class="flex items-center gap-3">
        <span class="hidden text-sm text-stone-400 sm:block">
            {{ auth()->user()->name }}
            <span class="ml-1 rounded bg-night-800 px-2 py-0.5 text-xs uppercase text-stone-400">
                {{ auth()->user()->getRoleNames()->implode(', ') }}
            </span>
        </span>
        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-brand-400 to-brand-600 text-sm font-semibold text-night-950">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>
    </div>
</header>