<?php

namespace App\Exceptions\Domain;

use Exception;

class StockMovementNotFoundException extends Exception
{
    protected $code = 404;
    protected $message = 'Không tìm thấy phiếu xuất/nhập kho.';
}
