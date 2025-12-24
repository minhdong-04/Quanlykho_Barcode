<?php

namespace App\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LowStockAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $productId;

    public function __construct($productId)
    {
        $this->productId = $productId;
    }

    public function handle()
    {
        // Find unnotified alerts for this product
        $alerts = \App\Models\LowStockAlert::where('product_id', $this->productId)->where('notified', false)->get();
        if ($alerts->isEmpty()) {
            return;
        }

        // Notify admin users only
        $users = \App\Models\User::where('role', 'admin')->get();
        $notification = new \App\Notifications\LowStockNotification($this->productId, $alerts->first()->current_quantity, $alerts->first()->reorder_level);
        \Illuminate\Support\Facades\Notification::send($users, $notification);

        // Mark alerts as notified
        foreach ($alerts as $alert) {
            $alert->notified = true;
            $alert->save();
        }
    }
}
