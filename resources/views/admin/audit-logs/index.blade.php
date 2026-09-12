@extends('layouts.app')

@section('title', 'Audit Log - '.config('app.name'))
@section('header', 'Audit Log')

@section('content')
    <h1 class="mb-5 text-2xl font-bold text-gray-900">Audit Log</h1>

    <div class="card mb-6 overflow-hidden">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-6">
            <div class="lg:col-span-2">
                <label class="label" for="f-audit-q">Pencarian</label>
                <input id="f-audit-q" type="search" name="q" value="{{ request('q') }}" placeholder="Modul, aksi, target..." class="input">
            </div>
            <div>
                <label class="label" for="f-audit-actor">Pengguna</label>
                <select id="f-audit-actor" name="actor" class="select">
                    <option value="">Semua</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected((string) request('actor') === (string) $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="f-audit-module">Modul</label>
                <select id="f-audit-module" name="module" class="select">
                    <option value="">Semua</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module }}" @selected(request('module') === $module)>{{ $module }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="f-audit-action">Aksi</label>
                <select id="f-audit-action" name="action" class="select">
                    <option value="">Semua</option>
                    @foreach ($actions as $action)
                        <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="f-audit-target">Target</label>
                <select id="f-audit-target" name="target" class="select">
                    <option value="">Semua</option>
                    @foreach ($targetTypes as $targetType)
                        <option value="{{ $targetType }}" @selected(request('target') === $targetType)>{{ $targetType }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="f-audit-from">Dari Tanggal</label>
                <input id="f-audit-from" type="date" name="date_from" value="{{ request('date_from') }}" class="input">
            </div>
            <div>
                <label class="label" for="f-audit-to">Sampai Tanggal</label>
                <input id="f-audit-to" type="date" name="date_to" value="{{ request('date_to') }}" class="input">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-w">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Waktu</th>
                        <th class="px-4 py-3 font-medium">Pengguna</th>
                        <th class="px-4 py-3 font-medium">IP</th>
                        <th class="px-4 py-3 font-medium">Modul</th>
                        <th class="px-4 py-3 font-medium">Aksi</th>
                        <th class="px-4 py-3 font-medium">Target</th>
                        <th class="px-4 py-3 font-medium">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($logs as $log)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $log->user?->name ?? (string) ($log->user_id ?: 'Sistem') }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $log->ip ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $log->module }}</td>
                            <td class="px-4 py-3">
                                <span class="badge bg-gray-100 text-gray-700">{{ $log->action }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                @if ($log->target_type && $log->target_id !== null)
                                    {{ $log->target_type }} #{{ $log->target_id }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($changes = $log->changes)
                                    <details>
                                        <summary class="cursor-pointer text-xs text-brand-600">Lihat perubahan</summary>
                                        <ul class="mt-2 max-h-40 space-y-1.5 overflow-auto rounded-lg bg-gray-50 p-3">
                                            @foreach ($changes as $key => $pair)
                                                <li class="text-xs">
                                                    <span class="font-semibold text-gray-700">{{ $key }}</span>
                                                    @if ($pair['old'] !== null && $pair['new'] !== null)
                                                        <span class="text-red-600 line-through">{{ is_scalar($pair['old']) ? $pair['old'] : json_encode($pair['old'], JSON_UNESCAPED_UNICODE) }}</span>
                                                        <span class="text-gray-400">&rarr;</span>
                                                        <span class="text-emerald-700">{{ is_scalar($pair['new']) ? $pair['new'] : json_encode($pair['new'], JSON_UNESCAPED_UNICODE) }}</span>
                                                    @else
                                                        <span class="text-emerald-700 font-medium">
                                                            {{ ($pair['new'] ?? $pair['old']) === true ? 'ya' : (($pair['new'] ?? $pair['old']) === false ? 'tidak' : ($pair['new'] ?? $pair['old'])) }}
                                                        </span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </details>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">Belum ada catatan audit.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-4">{{ $logs->links() }}</div>
    </div>
@endsection