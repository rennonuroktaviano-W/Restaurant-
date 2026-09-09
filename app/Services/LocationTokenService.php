<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;

class LocationTokenService
{
    public const TYPE_TABLE = 'table';

    public const TYPE_ROOM = 'room';

    public const TTL_SECONDS = 43200;

    /**
     * Issue a signed, time-limited token identifying a table or room (BR-015 dynamic QR).
     *
     * The token is opaque: it carries no raw DB identifiers the caller can read.
     */
    public function issue(string $type, int $id, ?int $ttl = self::TTL_SECONDS): string
    {
        $payload = base64_encode(json_encode([
            'type' => $type,
            'id' => $id,
            'exp' => now()->getTimestamp() + $ttl,
        ]));

        $signature = $this->sign($payload);

        return $payload.'.'.$signature;
    }

    /**
     * @return array{type: string, id: int}|null
     */
    public function verify(string $token, ?int $expiresAt = null): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 2) {
            return null;
        }

        [$payload, $signature] = $parts;

        if (! hash_equals($this->sign($payload), $signature)) {
            return null;
        }

        $decoded = json_decode(base64_decode($payload), true);

        if (! is_array($decoded) || ! isset($decoded['type'], $decoded['id'], $decoded['exp'])) {
            return null;
        }

        if ((int) $decoded['exp'] < now()->getTimestamp()) {
            return null;
        }

        if ($expiresAt !== null && (int) $decoded['exp'] < $expiresAt) {
            return null;
        }

        return [
            'type' => $decoded['type'],
            'id' => (int) $decoded['id'],
        ];
    }

    private function sign(string $payload): string
    {
        return hash_hmac('sha256', $payload, $this->key());
    }

    private function key(): string
    {
        return (string) Config::get('app.key');
    }
}
