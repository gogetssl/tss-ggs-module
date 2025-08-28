<?php

namespace ModulesGarden\TTSGGSModule\Core\App\Controllers\ResponseResolver;

interface ResolverInterface
{
    public function canResolve($response): bool;

    public function resolve($response);
}
