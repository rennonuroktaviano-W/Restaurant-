<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    public const CACHE_KEY = 'settings.all';

    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return Setting::query()
                ->pluck('value', 'key')
                ->map(fn ($value, $key) => $this->decode($key, $value, false))
                ->all();
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = Setting::where('key', $key)->value('value');

        if ($value === null) {
            return $default;
        }

        return $this->decode($key, $value, true);
    }

    public function set(string $key, mixed $value, ?string $label = null, string $group = Setting::GROUP_GENERAL): Setting
    {
        $store = $key === 'encrypted.secret'
            ? encrypt($value)
            : $this->encode($value);

        $setting = Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $store, 'label' => $label, 'group' => $group]
        );

        $this->flush();

        return $setting;
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function encode(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }

    private function decode(string $key, mixed $value, bool $rawStored): mixed
    {
        if ($rawStored && $key === 'encrypted.secret') {
            try {
                return decrypt($value);
            } catch (\Throwable) {
                return null;
            }
        }

        if ($value === 'true') {
            return true;
        }

        if ($value === 'false') {
            return false;
        }

        $json = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }

        return $value;
    }
}
