@extends('layouts.app')

@section('title', __('admin.audit_logs.title').' - '.config('app.name'))
@section('header', __('admin.audit_logs.title'))

@section('content')
    <h1 class="mb-5 text-2xl font-bold text-ink-900">{{ __('admin.audit_logs.title') }}</h1>

    <div class="card mb-6 overflow-hidden">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <label class="label">{{ __('admin.audit_logs.search') }}</label>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('admin.audit_logs.search_placeholder') }}" class="input">
            </div>
            <div>
                <label class="label">{{ __('admin.audit_logs.module') }}</label>
                <select name="module" class="select">
                    <option value="">{{ __('admin.all') }}</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module }}" {{ request('module') === $module ? 'selected' : '' }}>{{ $module }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">{{ __('admin.audit_logs.action') }}</label>
                <select name="action" class="select">
                    <option value="">{{ __('admin.all') }}</option>
                    @foreach ($actions as $action)
                        <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ $action }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn btn-primary">{{ __('admin.filter') }}</button>
                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-secondary">{{ __('admin.reset') }}</a>
            </div>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-admin">
                <thead>
                    <tr>
                        <th class="px-4 py-3 font-medium">{{ __('admin.audit_logs.time') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('admin.audit_logs.user') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('admin.audit_logs.module') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('admin.audit_logs.action') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('admin.audit_logs.target') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('admin.audit_logs.detail') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-900/10">
                    @forelse ($logs as $log)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-ink-600">{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                            <td class="px-4 py-3 text-sm text-ink-900">{{ $log->user?->name ?? (string) ($log->user_id ?: '-') }}</td>
                            <td class="px-4 py-3 text-sm text-ink-600">{{ $log->module }}</td>
                            <td class="px-4 py-3">
                                <span class="badge badge-ink">{{ $log->action }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-ink-600">
                                @if ($log->target_type && $log->target_id !== null)
                                    {{ $log->target_type }} #{{ $log->target_id }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($log->changes)
                                    <details>
                                        <summary class="cursor-pointer text-xs text-forest-600">{{ __('admin.audit_logs.view_changes') }}</summary>
                                        <pre class="mt-1 max-h-32 overflow-auto rounded bg-cream-100/50 p-2 text-[10px] text-ink-700">{{ json_encode($log->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </details>
                                @else
                                    <span class="text-xs text-ink-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-ink-500">{{ __('admin.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-4">{{ $logs->links('partials.pagination') }}</div>
    </div>
@endsection