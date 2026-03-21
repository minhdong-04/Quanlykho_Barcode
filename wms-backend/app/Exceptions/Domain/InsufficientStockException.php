<?php

namespace App\Exceptions\Domain;

use Exception;

class InsufficientStockException extends Exception
{
    protected $code = 422;
    protected $message = 'Số lượng tồn kho không đủ.';
}
