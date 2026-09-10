<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
            $table->foreignId('to_warehouse_id')->nullable()->after('warehouse_id')->constrained('warehouses')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->after('to_warehouse_id')->constrained()->nullOnDelete();
            $table->decimal('unit_cost', 14, 2)->nullable()->after('note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('warehouse_id');
            $table->dropConstrainedForeignId('to_warehouse_id');
            $table->dropConstrainedForeignId('supplier_id');
            $table->dropColumn('unit_cost');
        });
    }
};
