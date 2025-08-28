<?php

namespace ModulesGarden\TTSGGSModule\Core\App\Controllers\Exceptions;

class Unauthorized extends \Exception
{
    public function __construct(string $message = "", int $code = 401, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}