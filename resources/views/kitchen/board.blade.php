@extends('layouts.app')

@section('title', 'Kitchen Display - '.config('app.name'))
@section('header', 'Kitchen Display')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Kitchen Display</h1>
        <div class="flex items-center gap-3">
            <button id="kds-sound-toggle" type="button" class="btn btn-secondary !px-3 !py-1 text-xs" aria-pressed="false">Suara</button>
            <span id="board-clock" class="text-xl font-semibold tabular-nums text-gray-600">{{ now()->format('H:i:s') }}</span>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <section>
            <div class="mb-3 flex items-center justify-between rounded-t-lg bg-amber-500 px-4 py-2">
                <h2 class="text-sm font-bold uppercase text-white">Menunggu</h2>
                <span class="rounded-full bg-white px-2 py-0.5 text-xs font-bold text-amber-600">{{ $newOrders->count() }}</span>
            </div>
            <div class="space-y-3">
                @forelse ($newOrders as $order)
                    @include('kitchen._ticket', ['order' => $order, 'zone' => 'new'])
                @empty
                    <div class="card p-6 text-center text-sm text-gray-400">Tidak ada order dapur.</div>
                @endforelse
            </div>
        </section>

        <section>
            <div class="mb-3 flex items-center justify-between rounded-t-lg bg-orange-500 px-4 py-2">
                <h2 class="text-sm font-bold uppercase text-white">Dimasak</h2>
                <span class="rounded-full bg-white px-2 py-0.5 text-xs font-bold text-orange-600">{{ $cooking->count() }}</span>
            </div>
            <div class="space-y-3">
                @forelse ($cooking as $order)
                    @include('kitchen._ticket', ['order' => $order, 'zone' => 'cooking'])
                @empty
                    <div class="card p-6 text-center text-sm text-gray-400">Tidak ada menu dimasak.</div>
                @endforelse
            </div>
        </section>

        <section>
            <div class="mb-3 flex items-center justify-between rounded-t-lg bg-emerald-500 px-4 py-2">
                <h2 class="text-sm font-bold uppercase text-white">Siap Disajikan</h2>
                <span class="rounded-full bg-white px-2 py-0.5 text-xs font-bold text-emerald-600">{{ $ready->count() }}</span>
            </div>
            <div class="space-y-3">
                @forelse ($ready as $order)
                    @include('kitchen._ticket', ['order' => $order, 'zone' => 'ready'])
                @empty
                    <div class="card p-6 text-center text-sm text-gray-400">Belum ada order siap.</div>
                @endforelse
            </div>
        </section>
    </div>

    @push('scripts')
        <script>
            setInterval(() => {
                const el = document.getElementById('board-clock');
                if (el) el.textContent = new Date().toLocaleTimeString('id-ID', { hour12: false });
            }, 1000);

            // FR-KDS-005 - per-device sound toggle; visual board always remains the primary indicator.
            const soundKey = 'kds.sound';
            const stored = localStorage.getItem(soundKey);
            let soundEnabled = stored === null ? '1' : stored;
            const btn = document.getElementById('kds-sound-toggle');

            const syncSoundButton = () => {
                if (!btn) { return; }
                const on = soundEnabled === '1';
                btn.textContent = on ? 'Suara: Nyala' : 'Suara: Mati';
                btn.setAttribute('aria-pressed', String(on));
            };

            if (btn) {
                btn.addEventListener('click', () => {
                    soundEnabled = soundEnabled === '1' ? '0' : '1';
                    localStorage.setItem(soundKey, soundEnabled);
                    syncSoundButton();
                });
            }

            const beep = () => {
                if (soundEnabled !== '1') { return; }
                try {
                    const Ctx = window.AudioContext || window.webkitAudioContext;
                    const ctx = new Ctx();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.value = 880;
                    gain.gain.setValueAtTime(0.15, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.4);
                } catch (e) { /* audio blocked/unavailable */ }
            };

            syncSoundButton();

            if (window.EchoEnabled && window.Echo) {
                let reloadTimer = null;
                const scheduleReload = () => {
                    beep();
                    clearTimeout(reloadTimer);
                    reloadTimer = setTimeout(() => window.location.reload(), 800);
                };

                Echo.channel('kitchen')
                    .listen('.OrderCreated', scheduleReload)
                    .listen('.OrderStatusUpdated', scheduleReload);
            }
        </script>
    @endpush
@endsection