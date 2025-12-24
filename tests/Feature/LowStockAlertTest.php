<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class LowStockAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_low_stock_alerts_endpoint_returns_items()
    {
        // create user without factory
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);
        Sanctum::actingAs($user);

        $product = Product::create(['name' => 'Widget', 'sku' => 'W1', 'barcode' => '123', 'reorder_level' => 5]);
        $warehouse = Warehouse::create(['name' => 'Main']);

        Inventory::create(['product_id' => $product->id, 'warehouse_id' => $warehouse->id, 'quantity' => 3, 'reorder_level' => 5]);

        $response = $this->getJson('/api/v1/alerts/low-stock');
        $response->assertStatus(200);
        $response->assertJsonFragment(['product_id' => $product->id]);
    }
}
