<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AuditLogger;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(protected SettingsService $settings, protected AuditLogger $audit) {}

    public function index(): View
    {
        Gate::authorize('setting.manage');

        $groups = Setting::query()->orderBy('group')->orderBy('key')->get()->groupBy('group');

        return view('admin.settings.index', compact('groups'));
    }

    public function update(Request $request): RedirectResponse
    {
        Gate::authorize('setting.manage');

        $payload = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable'],
        ]);

        $old = Setting::query()->pluck('value', 'key')->all();

        foreach ($payload['settings'] as $key => $value) {
            $set = Setting::where('key', $key)->first();

            if (! $set) {
                continue;
            }

            $normalized = is_array($value) ? json_encode($value) : $value;

            if ((string) $set->value !== (string) $normalized) {
                $set->update(['value' => $normalized]);
            }
        }

        $this->settings->flush();

        $this->audit->log('update', 'settings', 'setting', null, $old, Setting::query()->pluck('value', 'key')->all());

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
