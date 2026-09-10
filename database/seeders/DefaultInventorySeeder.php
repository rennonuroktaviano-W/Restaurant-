<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

/**
 * Default warehouse + supplier used by the multi-warehouse inventory module.
 */
class DefaultInventorySeeder extends Seeder
{
    public function run(): void
    {
        Warehouse::firstOrCreate(
            ['slug' => 'gudang-utama'],
            ['name' => 'Gudang Utama', 'is_active' => true],
        );

        Supplier::firstOrCreate(
            ['name' => 'Pemasok Utama'],
            ['contact_person' => '', 'is_active' => true],
        );
    }
}
