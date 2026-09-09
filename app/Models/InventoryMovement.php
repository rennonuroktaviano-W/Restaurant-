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

    public $timestamps = false;

    protected $fillable = [
        'product_id', 'type', 'quantity', 'before', 'after',
        'reference_type', 'reference_id', 'actor_id', 'note', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'before' => 'integer',
            'after' => 'integer',
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
}
