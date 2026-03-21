<?php

namespace App\Exceptions\Domain;

use Exception;

class UnauthorizedException extends Exception
{
    protected $code = 403;
    protected $message = 'Bạn không có quyền thực hiện thao tác này.';
}
