@extends('layouts.app')

@section('title', 'Audit Log - '.config('app.name'))
@section('header', 'Audit Log')

@section('content')
    <h1 class="mb-5 text-2xl font-bold text-gray-900">Audit Log</h1>

    <div class="card mb-6 overflow-hidden">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <label class="label">Pencarian</label>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Modul, aksi, target..." class="input">
            </div>
            <div>
                <label class="label">Modul</label>
                <select name="module" class="select">
                    <option value="">Semua</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module }}" {{ request('module') === $module ? 'selected' : '' }}>{{ $module }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Aksi</label>
                <select name="action" class="select">
                    <option value="">Semua</option>
                    @foreach ($actions as $action)
                        <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ $action }}</option>
                    @endforeach
                </select>
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
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $log->user?->name ?? (string) ($log->user_id ?: '-') }}</td>
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
                                @if ($log->changes)
                                    <details>
                                        <summary class="cursor-pointer text-xs text-brand-600">Lihat perubahan</summary>
                                        <pre class="mt-1 max-h-32 overflow-auto rounded bg-gray-50 p-2 text-[10px] text-gray-700">{{ json_encode($log->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </details>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">Belum ada catatan audit.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-4">{{ $logs->links() }}</div>
    </div>
@endsection