<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'action', 'module', 'target_type', 'target_id',
        'old_values', 'new_values', 'ip', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getChangesAttribute(): array
    {
        return collect(array_unique(array_merge(array_keys($this->old_values ?? []), array_keys($this->new_values ?? []))))
            ->mapWithKeys(fn (string $key) => [$key => [
                'old' => $this->old_values[$key] ?? null,
                'new' => $this->new_values[$key] ?? null,
            ]])
            ->toArray();
    }
}
