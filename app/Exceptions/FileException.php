<?php

namespace App\Exceptions;

use Exception;

class FileException extends Exception
{
    public function __construct(string $message, $code = 0, Exception|null $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}