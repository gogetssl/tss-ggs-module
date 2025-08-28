<?php

namespace ModulesGarden\TTSGGSModule\Core\Services;

use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\Routing\Url;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Translator;
use ModulesGarden\TTSGGSModule\Core\UI\Breadcrumbs\Item;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;

class Breadcrumbs extends \ModulesGarden\TTSGGSModule\Core\UI\Breadcrumbs\Breadcrumbs
{
    public function __construct()
    {
        $this->addDefault();
    }

    protected function addDefault()
    {
        $route = \ModulesGarden\TTSGGSModule\Core\Support\Facades\Router::getCurrentRoute();
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
