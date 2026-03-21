<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('users')->where('role', 'staff')->update(['role' => 'warehouse_staff']);

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','manager','warehouse_staff') NOT NULL DEFAULT 'warehouse_staff'");
        }
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'warehouse_staff')->update(['role' => 'staff']);

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','staff') NOT NULL DEFAULT 'staff'");
        }
    }
};
