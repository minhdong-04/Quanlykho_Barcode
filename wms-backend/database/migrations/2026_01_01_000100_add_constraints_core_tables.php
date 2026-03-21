<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Enforce single inventory row per product + non-negative quantities
        Schema::table('inventories', function (Blueprint $table) {
            $table->unique('product_id');
        });

        // Add check constraints for inventories (MySQL 8.0+)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE inventories ADD CONSTRAINT chk_inventories_quantity_non_negative CHECK (quantity >= 0)');
            DB::statement('ALTER TABLE inventories ADD CONSTRAINT chk_inventories_reorder_non_negative CHECK (reorder_level >= 0)');
        }

        // Add constraints and indexes to stock_movements
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('created_at');
        });
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE stock_movements ADD CONSTRAINT chk_stock_movements_type CHECK (type in ('in','out'))");
            DB::statement('ALTER TABLE stock_movements ADD CONSTRAINT chk_stock_movements_quantity_nonzero CHECK (quantity <> 0)');
        }
    }

    public function down(): void
    {
        // Reverse stock_movements indexes and checks
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['created_at']);
        });
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE stock_movements DROP CHECK chk_stock_movements_type');
            DB::statement('ALTER TABLE stock_movements DROP CHECK chk_stock_movements_quantity_nonzero');
        }

        // Reverse inventory constraints
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE inventories DROP CHECK chk_inventories_quantity_non_negative');
            DB::statement('ALTER TABLE inventories DROP CHECK chk_inventories_reorder_non_negative');
        }
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropUnique(['product_id']);
        });
    }
};
