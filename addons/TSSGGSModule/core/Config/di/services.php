<?php

use ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\Addon\Config;
use ModulesGarden\TSSGGSModule\Core\DependencyInjection\PackageServices;
use ModulesGarden\TSSGGSModule\Core\Events\Dispatcher;
use ModulesGarden\TSSGGSModule\Core\Lang\Lang;
use ModulesGarden\TSSGGSModule\Core\Services\Breadcrumbs;
use ModulesGarden\TSSGGSModule\Core\Services\Messages;
use ModulesGarden\TSSGGSModule\Core\Services\Route;
use ModulesGarden\TSSGGSModule\Core\Services\Translator;
use ModulesGarden\TSSGGSModule\Core\Services\Validator;
use ModulesGarden\TSSGGSModule\Core\Session\Session;

return [
    Dispatcher::class,

    //New
    Translator::class,
    Validator::class,
    PackageServices::class,
    \ModulesGarden\TSSGGSModule\Core\Services\Params::class,
    Session::class,
    Breadcrumbs::class,
    Messages::class,
    \ModulesGarden\TSSGGSModule\Core\Services\Router::class,
    \ModulesGarden\TSSGGSModule\Core\Services\Smarty::class,
    \ModulesGarden\TSSGGSModule\Core\Services\Request::class,
    \ModulesGarden\TSSGGSModule\Core\Services\Config::class,
    \ModulesGarden\TSSGGSModule\Core\Services\Binder::class,
    \ModulesGarden\TSSGGSModule\Core\Services\Menu::class,
    Lang::class,
    \ModulesGarden\TSSGGSModule\Core\Services\Store::class
];
