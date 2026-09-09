<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'sku', 'name', 'slug', 'image', 'cost_price', 'sale_price',
        'stock_type', 'stock', 'is_active', 'is_available', 'is_featured', 'is_kitchen', 'sort_order', 'description',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'is_kitchen' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('is_available', true);
    }

    public function isLimitedStock(): bool
    {
        return $this->stock_type === 'limited';
    }

    public function isSoldOut(): bool
    {
        return $this->isLimitedStock() && $this->stock <= 0;
    }
}
