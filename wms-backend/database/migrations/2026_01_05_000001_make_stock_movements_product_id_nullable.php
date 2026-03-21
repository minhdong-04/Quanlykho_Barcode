<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite (tests) can't reliably alter FKs/columns in older versions.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // 1) Drop existing FK (currently ON DELETE CASCADE)
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        // 2) Make product_id nullable (raw SQL to avoid doctrine/dbal dependency)
        // MySQL: BIGINT UNSIGNED matches foreignId
        DB::statement('ALTER TABLE stock_movements MODIFY product_id BIGINT UNSIGNED NULL');

        // 3) Re-add FK with ON DELETE SET NULL to keep movement history when product is deleted
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Revert back to NOT NULL + ON DELETE CASCADE
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        DB::statement('ALTER TABLE stock_movements MODIFY product_id BIGINT UNSIGNED NOT NULL');

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
        });
    }
};
