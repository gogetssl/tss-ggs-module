<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

/**
 * A client input error of a code signing order: the message is a language key, the argument fills its "%s"
 * (an argument that is itself a language key - a field name - is translated too)
 */
class CodeSigningInputException extends \Exception
{
    public $argument;

    public function __construct($key, $argument = null)
    {
        parent::__construct($key);
        $this->argument = $argument;
    }
}
