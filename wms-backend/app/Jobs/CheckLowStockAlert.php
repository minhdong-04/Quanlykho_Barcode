<?php
namespace App\Jobs;

use App\Models\Product;
use App\Models\Alert;
use App\Notifications\SendStockAlertNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckLowStockAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $productId
    ) {}

    public function handle(): void
    {
        $product = Product::with('inventory')->find($this->productId);

        if (! $product || ! $product->inventory) {
            return;
        }

        if ($product->inventory->quantity < $product->reorder_level) {

            // 1. Lưu alert
            Alert::create([
                'product_id' => $product->id,
                'type' => 'low_stock',
                'message' => "Low stock: {$product->name}",
            ]);

            // 2. GỬI NOTIFICATION (ĐÚNG CÁCH)
            $product->user->notify(
                new SendStockAlertNotification(
                    $product->name,
                    $product->inventory->quantity
                )
            );
        }
    }
}
