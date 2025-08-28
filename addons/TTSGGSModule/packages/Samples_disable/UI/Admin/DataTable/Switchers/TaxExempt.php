<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Switchers;

use ModulesGarden\TTSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxOnActionInterface;
use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use WHMCS\User\Client;

class TaxExempt extends Switcher implements \ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface, AjaxOnActionInterface
{
    protected string $provider = \ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Providers\TaxExempt::class;
    protected string $providerAction = CrudProvider::ACTION_UPDATE;
}
