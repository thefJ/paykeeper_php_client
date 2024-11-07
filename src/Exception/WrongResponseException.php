<?php
declare(strict_types=1);

namespace ThefJ\PayKeeperClient\Exception;

use Exception;

class WrongResponseException extends Exception
{
    private const int HTTP_BAD_REQUEST_CODE = 400;

    public function __construct(string $message = "Wrong response", int $code = self::HTTP_BAD_REQUEST_CODE, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}