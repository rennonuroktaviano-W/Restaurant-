<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('audit.view');

        $logs = AuditLog::query()
            ->with('user')
            ->when($request->actor, fn ($q, $v) => $q->where('user_id', $v))
            ->when($request->action, fn ($q, $v) => $q->where('action', $v))
            ->when($request->module, fn ($q, $v) => $q->where('module', $v))
            ->when($request->target, fn ($q, $v) => $q->where('target_type', $v))
            ->when($request->filled('date_from'), fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->filled('date_to'), fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($request->q, fn ($q, $s) => $q->where(function ($w) use ($s) {
                $w->where('module', 'like', "%{$s}%")
                    ->orWhere('action', 'like', "%{$s}%")
                    ->orWhere('target_type', 'like', "%{$s}%")
                    ->orWhere('target_id', $s);
            }))
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        $modules = AuditLog::query()->distinct()->pluck('module');
        $actions = AuditLog::query()->distinct()->pluck('action');

        return view('admin.audit-logs.index', compact('logs', 'modules', 'actions'));
    }
}
