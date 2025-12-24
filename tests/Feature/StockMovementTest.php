<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_in_and_out_flow()
    {
        // create user and authenticate with Sanctum
        $user = User::create([
            'name' => 'Tester',
            'email' => 'tester@example.com',
            'password' => Hash::make('password'),
        ]);

        Sanctum::actingAs($user, ['*']);
        $this->withoutExceptionHandling();

        // create product and warehouse
        $product = Product::create(['name' => 'Widget', 'sku' => 'W123', 'barcode' => 'BAR123', 'cost_price' => 10, 'sell_price' => 15]);
        $warehouse = Warehouse::create(['name' => 'Main']);

        // stock in 50 units
        $resp = $this->postJson('/api/v1/stock/in', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 50,
        ]);

        $resp->assertStatus(201);
        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 50,
        ]);

        // stock out 20 units
        $resp2 = $this->postJson('/api/v1/stock/out', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 20,
        ]);

        $resp2->assertStatus(201);
        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 30,
        ]);
    }
}
