<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceStation extends Model
{
    use HasFactory;

    public const TYPE_KIOSK = 'kiosk';

    public const TYPE_CASHIER = 'cashier';

    public const TYPE_KITCHEN = 'kitchen';

    protected $fillable = ['name', 'station_code', 'type', 'area_id', 'is_active', 'last_seen_at'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}
