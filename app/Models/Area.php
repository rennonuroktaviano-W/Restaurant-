<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'slug', 'type', 'description', 'address', 'latitude', 'longitude', 'open_time', 'close_time', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
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
        if ($this->address && filter_var($this->address, FILTER_VALIDATE_URL)) {
            return $this->address;
        }

        if ($this->hasCoordinates()) {
            return 'https://www.google.com/maps/search/?api=1&query='.$this->latitude.','.$this->longitude;
        }

        return $this->address
            ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($this->address)
            : '#';
    }

    public function getEmbedUrlAttribute(): ?string
    {
        $query = $this->mapsSearchQuery();

        if ($query !== null) {
            return 'https://maps.google.com/maps?q='.$query.'&z=15&output=embed';
        }

        if ($this->hasCoordinates()) {
            return 'https://maps.google.com/maps?q='.$this->latitude.','.$this->longitude.'&z=15&output=embed';
        }

        return null;
    }

    public function getHoursLabelAttribute(): ?string
    {
        if (! $this->open_time || ! $this->close_time) {
            return null;
        }

        return $this->open_time.' - '.$this->close_time;
    }

    private function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    private function mapsSearchQuery(): ?string
    {
        if (! $this->address) {
            return null;
        }

        parse_str(parse_url($this->address, PHP_URL_QUERY) ?? '', $params);

        $q = $params['query'] ?? $params['q'] ?? null;

        if ($q !== null && $q !== '') {
            return $q;
        }

        if (preg_match('#/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)#', $this->address, $m)) {
            return $m[1].','.$m[2];
        }

        return null;
    }
}
