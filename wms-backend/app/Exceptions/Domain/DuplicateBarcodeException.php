<?php

namespace App\Exceptions\Domain;

use Exception;

class DuplicateBarcodeException extends Exception
{
    protected $code = 409;
    protected $message = 'Barcode đã tồn tại.';
}
