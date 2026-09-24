@php
    $location = session('location');
    $type = $location['type'] ?? null;
    $id = $location['id'] ?? null;
    $name = 'Lokasi';
    $areaName = '';

    if ($type === \App\Services\LocationTokenService::TYPE_TABLE) {
        $table = \App\Models\DiningTable::with('area')->find($id);
        if ($table) {
            $name = $table->name;
            $areaName = $table->area?->name ?? '';
        }
    } elseif ($type === \App\Services\LocationTokenService::TYPE_ROOM) {
        $room = \App\Models\Room::with('area')->find($id);
        if ($room) {
            $name = $room->name;
            $areaName = $room->area?->name ?? '';
        }
    }
@endphp

@if ($type && $id)
    <template x-teleport="body">
        <div x-data="locationConfirm" x-show="showLocationConfirm" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-ink-900/60 p-4" @keydown.escape.window="showLocationConfirm = false">
            <div class="w-full max-w-md rounded-lg border border-ink-900/10 bg-cream-50 p-6 shadow-2xl" @click.outside="showLocationConfirm = false" role="dialog" aria-modal="true" aria-labelledby="location-confirm-title">
                <div class="text-center py-2">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-forest-100 text-forest-700">
                        @if ($type === \App\Services\LocationTokenService::TYPE_TABLE)
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 22V12h6v10"/></svg>
                        @endif
                    </div>

                    <h3 id="location-confirm-title" class="font-display text-lg font-semibold text-ink-900">Pesan di sini?</h3>
                    <p class="mt-2 text-sm text-ink-600">
                        <span class="font-medium">{{ $name }}</span>
                        @if ($areaName)
                            <span class="text-ink-500"> · {{ $areaName }}</span>
                        @endif
                    </p>

                    <p class="mt-4 text-sm text-ink-500">Anda akan memesan untuk lokasi di atas. Lanjutkan?</p>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="button" @click="showLocationConfirm = false" class="btn btn-secondary flex-1">Ganti Lokasi</button>
                    <button type="button" @click="confirmLocation()" class="btn btn-primary flex-1">Ya, Pesan di Sini</button>
                </div>
            </div>
        </div>
    </template>
@endif