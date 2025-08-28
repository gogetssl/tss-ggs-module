<?php

namespace ModulesGarden\TTSGGSModule\Core\App\Controllers\ResponseResolver\Resolvers;

use ModulesGarden\TTSGGSModule\Core\App\Controllers\ResponseResolver\ResolverInterface;
use ModulesGarden\TTSGGSModule\Core\UI\ViewAjax;

class AjaxViewResolver implements ResolverInterface
{
    public function canResolve($response): bool
    {
        return false;

        return get_class($response) == ViewAjax::class;
    }

    public function resolve($response)
    {
        die('adawdwad');
    }
}
