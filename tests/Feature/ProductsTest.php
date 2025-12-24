<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_crud()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        Sanctum::actingAs($admin, ['*']);

        // index
        for ($i = 1; $i <= 3; $i++) {
            Product::create([
                'name' => "P{$i}",
                'sku' => "SKU{$i}",
                'barcode' => (string)(1000000000000 + $i),
                'description' => 'desc',
                'reorder_level' => 5,
            ]);
        }
        $res = $this->getJson('/api/v1/products');
        $res->assertStatus(200)->assertJsonStructure(['data']);

        // store
        $payload = [
            'name' => 'Test Product',
            'sku' => 'TP-001',
            'barcode' => '1234567890123',
            'description' => 'desc',
            'reorder_level' => 5,
        ];

        $res = $this->postJson('/api/v1/products', $payload);
        $res->assertStatus(201)->assertJsonPath('name', 'Test Product');
        $this->assertDatabaseHas('products', ['sku' => 'TP-001']);

        $productId = Product::where('sku', 'TP-001')->value('id');

        // show
        $res = $this->getJson("/api/v1/products/{$productId}");
        $res->assertStatus(200)->assertJsonPath('id', $productId);

        // update
        $res = $this->putJson("/api/v1/products/{$productId}", ['name' => 'Updated']);
        $res->assertStatus(200)->assertJsonPath('name', 'Updated');
        $this->assertDatabaseHas('products', ['id' => $productId, 'name' => 'Updated']);

        // delete
        $res = $this->deleteJson("/api/v1/products/{$productId}");
        $res->assertStatus(204);
        $this->assertDatabaseMissing('products', ['id' => $productId]);
    }
}
