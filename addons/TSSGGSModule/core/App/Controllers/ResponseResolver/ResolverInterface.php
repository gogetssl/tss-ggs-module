<?php

namespace ModulesGarden\TSSGGSModule\Core\App\Controllers\ResponseResolver;

interface ResolverInterface
{
    public function canResolve($response): bool;

    public function resolve($response);
}
