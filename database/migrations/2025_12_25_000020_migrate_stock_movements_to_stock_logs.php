<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (! Schema::hasTable('stock_movements')) {
            return;
        }

        if (! Schema::hasTable('stock_logs')) {
            // create minimal stock_logs table if missing
            Schema::create('stock_logs', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
                $table->integer('quantity_change');
                $table->string('action');
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // Copy data from stock_movements -> stock_logs mapping fields
        $moved = DB::table('stock_movements')->select('*')->get();
        $batch = [];
        foreach ($moved as $row) {
            $batch[] = [
                'product_id' => $row->product_id,
                'warehouse_id' => $row->warehouse_id,
                'quantity_change' => ($row->movement_type === 'out') ? -1 * (int)$row->quantity : (int)$row->quantity,
                'action' => $row->movement_type,
                'reference_id' => $row->supplier_id ?? $row->related_id ?? null,
                'notes' => $row->notes,
                'user_id' => $row->user_id,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ];

            if (count($batch) >= 500) {
                DB::table('stock_logs')->insert($batch);
                $batch = [];
            }
        }
        if (! empty($batch)) {
            DB::table('stock_logs')->insert($batch);
        }

        // After copying, drop old table
        Schema::dropIfExists('stock_movements');
    }

    public function down()
    {
        // Cannot fully restore dropped table with original ids reliably; recreate empty table
        if (! Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
                $table->integer('quantity');
                $table->enum('movement_type', ['in', 'out', 'transfer']);
                $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
                $table->unsignedBigInteger('related_id')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }
};
