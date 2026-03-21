<?php

namespace App\Exceptions\Domain;

use Exception;

class InvalidBarcodeException extends Exception
{
    protected $code = 404;
    protected $message = 'Barcode không hợp lệ hoặc không tìm thấy.';
}
