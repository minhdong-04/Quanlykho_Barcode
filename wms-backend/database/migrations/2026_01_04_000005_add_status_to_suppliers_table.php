<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'status')) {
                $table->string('status', 20)->default('active')->after('address');
                $table->index('status');
            }
        });
    }

    public function down(): void
    {
        // SQLite (tests) can't reliably drop columns in older versions.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'status')) {
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            }
        });
    }
};
