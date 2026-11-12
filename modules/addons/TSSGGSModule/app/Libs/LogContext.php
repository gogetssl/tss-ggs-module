<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

/**
 * Extra data for a Module Log entry, so "Show details" says more than the bare service ID
 */
class LogContext
{
    /**
     * The entry data of a failure: where the exception was thrown and its class, next to the given data
     */
    public static function exception(\Throwable $e, array $data = []): array
    {
        $data['exception'] = [
            'class'    => get_class($e),
            'code'     => $e->getCode(),
            'location' => basename($e->getFile()) . ':' . $e->getLine(),
        ];

        return $data;
    }
}
