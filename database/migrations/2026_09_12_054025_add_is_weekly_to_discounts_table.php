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
        Schema::table('discounts', function (Blueprint $table) {
            $table->boolean('is_weekly')->default(false)->after('is_active');

            $table->index(['is_weekly', 'is_active', 'starts_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropIndex(['is_weekly', 'is_active', 'starts_at']);
            $table->dropColumn('is_weekly');
        });
    }
};
