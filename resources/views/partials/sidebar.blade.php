<aside class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-night-700 bg-night-900 transition-transform duration-200 lg:translate-x-0" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
    <div class="flex h-16 items-center gap-2 border-b border-night-700 px-6">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-brand-400 to-brand-600 text-night-950 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <span class="flex-1 text-sm font-semibold text-stone-100">{{ config('app.name') }}</span>
        <button type="button" class="rounded-lg p-2 text-stone-300 transition hover:bg-night-800 lg:hidden" aria-label="Tutup menu navigasi" @click="sidebarOpen = false">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <nav @click="sidebarOpen = false" class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="Navigasi utama">
        @if (auth()->user()->can('setting.manage'))
            <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-stone-500">Overview</p>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'bg-brand-500/15 text-brand-300' : 'bg-night-900 text-stone-100 hover:bg-night-750' }} flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Dashboard
            </a>
        @endif

        @if (auth()->user()->can('order.view') || auth()->user()->can('kitchen.view'))
            <p class="px-3 pb-1 pt-4 text-[11px] font-semibold uppercase tracking-wider text-stone-500">Operasional</p>
        @endif
        @if (auth()->user()->can('order.view'))
            <a href="{{ route('cashier.dashboard') }}" class="{{ request()->routeIs('cashier.*') ? 'bg-brand-500/15 text-brand-300' : 'bg-night-900 text-stone-100 hover:bg-night-750' }} flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                Kasir
            </a>
        @endif

        @if (auth()->user()->can('kitchen.view'))
            <a href="{{ route('kitchen.dashboard') }}" class="{{ request()->routeIs('kitchen.*') ? 'bg-brand-500/15 text-brand-300' : 'bg-night-900 text-stone-100 hover:bg-night-750' }} flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8m-4-8v8m8-8v8M3 8l9-4 9 4M6 16h12"/></svg>
                Dapur
            </a>
        @endif

        @if (auth()->user()->can('catalog.manage') || auth()->user()->can('location.manage'))
            <p class="px-3 pb-1 pt-4 text-[11px] font-semibold uppercase tracking-wider text-stone-500">Katalog &amp; Lokasi</p>
        @endif
        @if (auth()->user()->can('catalog.manage'))
            <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') || request()->routeIs('admin.products.*') ? 'bg-brand-500/15 text-brand-300' : 'bg-night-900 text-stone-100 hover:bg-night-750' }} flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Kategori Menu
            </a>
        @endif

        @if (auth()->user()->can('location.manage'))
            <a href="{{ route('admin.areas.index') }}" class="{{ request()->routeIs('admin.areas.*') ? 'bg-brand-500/15 text-brand-300' : 'bg-night-900 text-stone-100 hover:bg-night-750' }} flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Area
            </a>
            <a href="{{ route('admin.dining-tables.index') }}" class="{{ request()->routeIs('admin.dining-tables.*') ? 'bg-brand-500/15 text-brand-300' : 'bg-night-900 text-stone-100 hover:bg-night-750' }} flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Meja Makan
            </a>
            <a href="{{ route('admin.rooms.index') }}" class="{{ request()->routeIs('admin.rooms.*') ? 'bg-brand-500/15 text-brand-300' : 'bg-night-900 text-stone-100 hover:bg-night-750' }} flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10"/></svg>
                Room
            </a>
        @endif

        @if (auth()->user()->can('inventory.update'))
            <p class="px-3 pb-1 pt-4 text-[11px] font-semibold uppercase tracking-wider text-stone-500">Inventori</p>
            <a href="{{ route('admin.inventory.index') }}" class="{{ request()->routeIs('admin.inventory.*') ? 'bg-brand-500/15 text-brand-300' : 'bg-night-900 text-stone-100 hover:bg-night-750' }} flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Inventori
            </a>
            <a href="{{ route('admin.warehouses.index') }}" class="{{ request()->routeIs('admin.warehouses.*') ? 'bg-brand-500/15 text-brand-300' : 'bg-night-900 text-stone-100 hover:bg-night-750' }} flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h4v-9h10v9h4M3 21v-8l1-1m0 0L9 3h6l5 9M10 9h4"/></svg>
                Gudang
            </a>
            <a href="{{ route('admin.suppliers.index') }}" class="{{ request()->routeIs('admin.suppliers.*') ? 'bg-brand-500/15 text-brand-300' : 'bg-night-900 text-stone-100 hover:bg-night-750' }} flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18l-2 4m0 0v10a1 1 0 01-1 1H6a1 1 0 01-1-1V8m14 0H4m2 0l-2 4h16l-2-4"/></svg>
                Pemasok
            </a>
        @endif

        @if (auth()->user()->can('discount.manage') || auth()->user()->can('report.export') || auth()->user()->can('report.view') || auth()->user()->can('payment.refund') || auth()->user()->can('setting.manage'))
            <p class="px-3 pb-1 pt-4 text-[11px] font-semibold uppercase tracking-wider text-stone-500">Keuangan</p>
        @endif
        @if (auth()->user()->can('discount.manage'))
            <a href="{{ route('admin.discounts.index') }}" class="{{ request()->routeIs('admin.discounts.*') ? 'bg-brand-500/15 text-brand-300' : 'bg-night-900 text-stone-100 hover:bg-night-750' }} flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M8 10a2 2 0 100-4 2 2 0 000 4zm8 8a2 2 0 100-4 2 2 0 000 4z"/></svg>
                Promo
            </a>
        @endif

        @if (auth()->user()->can('report.export') || auth()->user()->can('report.view'))
            <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'bg-brand-500/15 text-brand-300' : 'bg-night-900 text-stone-100 hover:bg-night-750' }} flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Laporan
            </a>
        @endif

        @if (auth()->user()->can('payment.refund'))
            <a href="{{ route('admin.refunds.index') }}" class="{{ request()->routeIs('admin.refunds.*') ? 'bg-brand-500/15 text-brand-300' : 'bg-night-900 text-stone-100 hover:bg-night-750' }} flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                Refund
            </a>
        @endif

        @if (auth()->user()->can('setting.manage'))
            <a href="{{ route('admin.payment-methods.index') }}" class="{{ request()->routeIs('admin.payment-methods.*') ? 'bg-brand-500/15 text-brand-300' : 'bg-night-900 text-stone-100 hover:bg-night-750' }} flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h4m8 3H5a2 2 0 01-2-2V8a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2z"/></svg>
                Metode Bayar
            </a>
        @endif

        @if (auth()->user()->can('user.manage') || auth()->user()->can('audit.view') || auth()->user()->can('setting.manage'))
            <p class="px-3 pb-1 pt-4 text-[11px] font-semibold uppercase tracking-wider text-stone-500">Sistem</p>
        @endif
        @if (auth()->user()->can('user.manage'))
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'bg-brand-500/15 text-brand-300' : 'bg-night-900 text-stone-100 hover:bg-night-750' }} flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Pengguna
            </a>
        @endif

        @if (auth()->user()->can('audit.view'))
            <a href="{{ route('admin.audit-logs.index') }}" class="{{ request()->routeIs('admin.audit-logs.*') ? 'bg-brand-500/15 text-brand-300' : 'bg-night-900 text-stone-100 hover:bg-night-750' }} flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Audit Log
            </a>
        @endif

        @if (auth()->user()->can('setting.manage'))
            <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'bg-brand-500/15 text-brand-300' : 'bg-night-900 text-stone-100 hover:bg-night-750' }} flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Pengaturan
            </a>
        @endif
    </nav>

    <div class="border-t border-night-700 p-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-stone-200 hover:bg-night-800 hover:text-stone-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Keluar
            </button>
        </form>
    </div>
</aside>