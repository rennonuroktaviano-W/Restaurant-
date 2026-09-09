<header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 sm:px-6">
    <div>
        <h1 class="text-lg font-semibold text-gray-900">@yield('header', 'Dashboard')</h1>
    </div>

    <div class="flex items-center gap-3">
        <span class="hidden text-sm text-gray-500 sm:block">
            {{ auth()->user()->name }}
            <span class="ml-1 rounded bg-gray-100 px-2 py-0.5 text-xs uppercase text-gray-600">
                {{ auth()->user()->getRoleNames()->implode(', ') }}
            </span>
        </span>
        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-600 text-sm font-semibold text-white">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>
    </div>
</header>