<header class="topbar">
    <div>
        <h1 class="text-lg font-semibold text-ink-900">@yield('header', __('nav.dashboard'))</h1>
    </div>

    <div class="flex items-center gap-3">
        <div class="hidden sm:flex sm:flex-col sm:items-end sm:gap-0.5">
            <span class="text-sm font-medium text-ink-800">{{ auth()->user()->name }}</span>
            <span class="text-xs text-ink-500">{{ auth()->user()->getRoleNames()->implode(', ') }}</span>
        </div>

        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button type="button"
                    @click="open = !open"
                    :aria-expanded="open"
                    aria-haspopup="true"
                    class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-forest-500 to-forest-700 text-cream-50 font-medium shadow-sm transition hover:from-forest-600 hover:to-forest-800"
                    aria-label="{{ __('common.user_menu') }}">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </button>

            <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 transform -translate-y-1" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-1" class="absolute right-0 mt-2 w-48 rounded-xl border border-ink-900/10 bg-cream-50 shadow-lg ring-1 ring-ink-900/5" role="menu">
                <div class="px-4 py-2 border-b border-ink-900/10">
                    <p class="text-sm font-medium text-ink-900">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-ink-500">{{ auth()->user()->email }}</p>
                </div>
                <a href="#" class="dropdown-item" role="menuitem">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    {{ __('common.profile') }}
                </a>
                <a href="#" class="dropdown-item" role="menuitem">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    {{ __('common.settings') }}
                </a>
                <hr class="border-ink-900/10 my-1">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item w-full text-left text-burgundy-600" role="menuitem">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        {{ __('auth.logout') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>