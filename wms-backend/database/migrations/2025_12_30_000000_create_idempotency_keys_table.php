<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('request_method')->nullable();
            $table->string('request_path')->nullable();
            $table->string('request_hash')->nullable();
            $table->json('response_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotency_keys');
    }
};
