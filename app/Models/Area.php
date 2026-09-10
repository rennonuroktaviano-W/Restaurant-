<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'slug', 'type', 'description', 'address', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function diningTables()
    {
        return $this->hasMany(DiningTable::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function getMapsUrlAttribute(): string
    {
        return $this->address
            ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($this->address)
            : '#';
    }
}
