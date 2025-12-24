<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\LowStockAlert;
use App\Models\User;
use App\Models\Product;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\Notification;

class LowStockJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_sends_notification_and_marks_alerts()
    {
        Notification::fake();

        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('secret'), 'role' => 'admin']);
        $regular = User::create(['name' => 'User', 'email' => 'user@example.com', 'password' => bcrypt('secret'), 'role' => 'user']);
        $product = Product::create(['name' => 'P', 'sku' => 'P1', 'barcode' => 'B1', 'reorder_level' => 5]);

        $alert = LowStockAlert::create(['product_id' => $product->id, 'current_quantity' => 2, 'reorder_level' => 5, 'notified' => false]);

        $job = new \App\Jobs\LowStockAlertJob($product->id);
        $job->handle();

        Notification::assertSentTo([$admin], LowStockNotification::class);
        Notification::assertNotSentTo([$regular], LowStockNotification::class);

        $this->assertTrue((bool) $alert->fresh()->notified);
    }

    public function test_stock_in_service_dispatches_job()
    {
        \Illuminate\Support\Facades\Bus::fake();

        $product = Product::create(['name' => 'P2', 'sku' => 'P2', 'barcode' => 'B2', 'reorder_level' => 10]);
        $warehouse = \App\Models\Warehouse::create(['name' => 'W']);
        \App\Models\Inventory::create(['product_id' => $product->id, 'warehouse_id' => $warehouse->id, 'quantity' => 0, 'reorder_level' => 10]);

        $service = new \App\Services\Inventory\StockInService();
        $service->handle($product->id, $warehouse->id, 1);

        \Illuminate\Support\Facades\Bus::assertDispatched(\App\Jobs\LowStockAlertJob::class);
    }
}
