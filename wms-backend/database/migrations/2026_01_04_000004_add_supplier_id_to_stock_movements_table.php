<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_movements', 'supplier_id')) {
                $table->foreignId('supplier_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('suppliers')
                    ->nullOnDelete();

                $table->index('supplier_id');
            }
        });
    }

    public function down(): void
    {
        // SQLite (tests) can't reliably drop FKs/columns in older versions.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('stock_movements', function (Blueprint $table) {
            if (Schema::hasColumn('stock_movements', 'supplier_id')) {
                $table->dropConstrainedForeignId('supplier_id');
                $table->dropIndex(['supplier_id']);
            }
        });
    }
};
