<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockChanged
{
    use Dispatchable, SerializesModels;

    public $productId;
    public $change;

    public function __construct($productId, $change)
    {
        $this->productId = $productId;
        $this->change = $change;
    }
}
