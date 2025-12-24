<?php

namespace App\Listeners;

use App\Events\StockChanged;

class SendLowStockNotification
{
    public function handle(StockChanged $event)
    {
        // check low stock and notify
    }
}
