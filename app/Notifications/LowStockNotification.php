<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    protected $productId;
    protected $currentQuantity;
    protected $reorderLevel;

    public function __construct($productId, $currentQuantity, $reorderLevel)
    {
        $this->productId = $productId;
        $this->currentQuantity = $currentQuantity;
        $this->reorderLevel = $reorderLevel;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'product_id' => $this->productId,
            'current_quantity' => $this->currentQuantity,
            'reorder_level' => $this->reorderLevel,
            'message' => 'Low stock for product ' . $this->productId,
        ];
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
                    ->subject('Low stock alert: product ' . $this->productId)
                    ->line('Product ID: ' . $this->productId)
                    ->line('Current quantity: ' . $this->currentQuantity)
                    ->line('Reorder level: ' . $this->reorderLevel);
    }
}
