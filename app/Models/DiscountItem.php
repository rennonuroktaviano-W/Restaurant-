<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountItem extends Model
{
    use HasFactory;

    protected $fillable = ['discount_id', 'target_type', 'target_id', 'quantity_required'];

    protected function casts(): array
    {
        return [
            'quantity_required' => 'integer',
        ];
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }
}
