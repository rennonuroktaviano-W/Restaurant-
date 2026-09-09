@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="mt-10">
        <div class="flex items-center justify-between gap-4">
            <p class="text-sm text-ink-500">
                Menampilkan
                @if ($paginator->firstItem())
                    <span class="font-medium text-ink-800">{{ $paginator->firstItem() }}</span>
                    –
                    <span class="font-medium text-ink-800">{{ $paginator->lastItem() }}</span>
                @else
                    {{ $paginator->count() }}
                @endif
                dari
                <span class="font-medium text-ink-800">{{ $paginator->total() }}</span>
                menu
            </p>

            @if ($paginator->hasPages())
                <div class="flex items-center gap-1">
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-ink-900/15 text-ink-300" aria-label="Halaman sebelumnya">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya" class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-ink-900/15 bg-cream-50 text-ink-700 transition hover:border-forest-600/40 hover:text-forest-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                    @endif

                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span aria-disabled="true" class="inline-flex h-11 min-w-11 items-center justify-center px-2 text-sm text-ink-400">{{ $element }}</span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="inline-flex h-11 min-w-11 items-center justify-center rounded-lg bg-forest-700 px-2 font-semibold text-cream-50">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" aria-label="Ke halaman {{ $page }}" class="inline-flex h-11 min-w-11 items-center justify-center rounded-lg border border-ink-900/15 bg-cream-50 px-2 text-sm text-ink-700 transition hover:border-forest-600/40 hover:text-forest-700">{{ $page }}</a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya" class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-ink-900/15 bg-cream-50 text-ink-700 transition hover:border-forest-600/40 hover:text-forest-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <span aria-disabled="true" class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-ink-900/15 text-ink-300" aria-label="Halaman berikutnya">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </nav>
@endif