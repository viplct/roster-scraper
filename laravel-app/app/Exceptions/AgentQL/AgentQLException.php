<?php

namespace App\Exceptions\AgentQL;

use Exception;

class AgentQLException extends Exception
{
    public function __construct(string $message = '', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
} 