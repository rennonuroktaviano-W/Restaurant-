<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id', 'product_id', 'quantity', 'min_threshold', 'max_capacity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'min_threshold' => 'integer',
            'max_capacity' => 'integer',
        ];
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function isLow(): bool
    {
        return $this->quantity <= $this->min_threshold;
    }
}
