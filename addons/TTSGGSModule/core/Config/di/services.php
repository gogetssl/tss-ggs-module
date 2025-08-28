<?php

use ModulesGarden\TTSGGSModule\Core\App\Controllers\Instances\Addon\Config;
use ModulesGarden\TTSGGSModule\Core\DependencyInjection\PackageServices;
use ModulesGarden\TTSGGSModule\Core\Events\Dispatcher;
use ModulesGarden\TTSGGSModule\Core\Lang\Lang;
use ModulesGarden\TTSGGSModule\Core\Services\Breadcrumbs;
use ModulesGarden\TTSGGSModule\Core\Services\Messages;
use ModulesGarden\TTSGGSModule\Core\Services\Route;
use ModulesGarden\TTSGGSModule\Core\Services\Translator;
use ModulesGarden\TTSGGSModule\Core\Services\Validator;
use ModulesGarden\TTSGGSModule\Core\Session\Session;

return [
    Dispatcher::class,

    //New
    Translator::class,
    Validator::class,
    PackageServices::class,
    \ModulesGarden\TTSGGSModule\Core\Services\Params::class,
    Session::class,
    Breadcrumbs::class,
    Messages::class,
    \ModulesGarden\TTSGGSModule\Core\Services\Router::class,
    \ModulesGarden\TTSGGSModule\Core\Services\Smarty::class,
    \ModulesGarden\TTSGGSModule\Core\Services\Request::class,
    \ModulesGarden\TTSGGSModule\Core\Services\Config::class,
    \ModulesGarden\TTSGGSModule\Core\Services\Binder::class,
    \ModulesGarden\TTSGGSModule\Core\Services\Menu::class,
    Lang::class,
    \ModulesGarden\TTSGGSModule\Core\Services\Store::class
];
