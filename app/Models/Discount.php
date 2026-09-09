<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_PERCENTAGE = 'percentage';

    public const TYPE_FIXED = 'fixed';

    protected $fillable = [
        'name', 'code', 'type', 'value', 'min_amount', 'max_amount',
        'is_automatic', 'starts_at', 'ends_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_amount' => 'decimal:2',
            'max_amount' => 'decimal:2',
            'is_automatic' => 'boolean',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function items()
    {
        return $this->hasMany(DiscountItem::class);
    }

    public function isApplicableAt(Carbon $time): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && $time->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $time->gt($this->ends_at)) {
            return false;
        }

        return true;
    }
}
