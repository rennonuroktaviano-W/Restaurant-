<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    public function log(
        string $action,
        string $module,
        ?string $targetType = null,
        ?int $targetId = null,
        array $oldValues = [],
        array $newValues = [],
        ?int $userId = null,
    ): AuditLog {
        $filteredOld = $this->filterSensitive($oldValues);
        $filteredNew = $this->filterSensitive($newValues);

        return AuditLog::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'module' => $module,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'old_values' => $filteredOld,
            'new_values' => $filteredNew,
            'ip' => Request::ip(),
            'created_at' => now(),
        ]);
    }

    private function filterSensitive(array $values): array
    {
        $keys = ['password', 'remember_token', 'token', 'secret', 'api_key', 'card_number', 'cvv'];

        foreach ($keys as $key) {
            if (array_key_exists($key, $values)) {
                $values[$key] = '[REDACTED]';
            }
        }

        return $values;
    }
}
