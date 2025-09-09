<?php

namespace ModulesGarden\TSSGGSModule\Core\Services;

use ModulesGarden\TSSGGSModule\Core\ModuleConstants;
use ModulesGarden\TSSGGSModule\Core\Routing\Url;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Translator;
use ModulesGarden\TSSGGSModule\Core\UI\Breadcrumbs\Item;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;

class Breadcrumbs extends \ModulesGarden\TSSGGSModule\Core\UI\Breadcrumbs\Breadcrumbs
{
    public function __construct()
    {
        $this->addDefault();
    }

    protected function addDefault()
    {
        $route = \ModulesGarden\TSSGGSModule\Core\Support\Facades\Router::getCurrentRoute();
        if (!$route)
        {
            return;
        }

        $level = ModuleConstants::getLevel();
        $this->add(new Item(Translator::get($level . '.breadcrumbs.' . $route->getName()), Url::route($route->getName())));

        if ($route->getAction() && $route->getAction() != ModuleConstants::DEFAULT_CONTROLLER_ACTION)
        {
            $this->add(new Item(Translator::get($level . '.breadcrumbs.' . $route->getName() . '_' . $route->getAction()), Url::route($route->getName() . '@' . $route->getAction(), Request::query()->all())));
        }
    }
}
