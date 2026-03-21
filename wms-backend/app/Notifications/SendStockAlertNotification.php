<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SendStockAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $productName,
        public int $quantity
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail']; // có thể bỏ mail nếu không cần
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Low Stock Alert')
            ->line("Product {$this->productName} is low on stock.")
            ->line("Current quantity: {$this->quantity}")
            ->line('Please restock as soon as possible.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'product' => $this->productName,
            'quantity' => $this->quantity,
        ];
    }
}
