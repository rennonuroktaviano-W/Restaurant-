<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;

    public const TYPE_IN = 'IN';

    public const TYPE_OUT = 'OUT';

    public const TYPE_ADJUSTMENT = 'ADJUSTMENT';

    public const TYPE_REVERSAL = 'REVERSAL';

    public const TYPE_TRANSFER = 'TRANSFER';

    public $timestamps = false;

    protected $fillable = [
        'product_id', 'warehouse_id', 'to_warehouse_id', 'supplier_id', 'type', 'quantity',
        'before', 'after', 'reference_type', 'reference_id', 'actor_id',
        'note', 'unit_cost', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'before' => 'integer',
            'after' => 'integer',
            'unit_cost' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
