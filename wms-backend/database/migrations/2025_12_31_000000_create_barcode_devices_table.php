<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('barcode_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('device_name'); // e.g., "Zebra DS3678", "USB Scanner #1"
            $table->enum('device_type', ['bluetooth', 'usb', 'camera']); // Type of device
            $table->string('browser_fingerprint'); // Browser/device identifier
            $table->timestamp('last_active_at')->nullable();
            $table->boolean('is_active')->default(true); // Admin can block device
            $table->timestamps();

            // Indexes for common queries
            $table->index('user_id');
            $table->index('is_active');
            $table->index('last_active_at');
            $table->index(['user_id', 'is_active']); // Composite index for listing active devices
        });
    }

    public function down()
    {
        Schema::dropIfExists('barcode_devices');
    }
};
