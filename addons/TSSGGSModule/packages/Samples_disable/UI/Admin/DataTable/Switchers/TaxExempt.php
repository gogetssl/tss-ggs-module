<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\Switchers;

use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxOnActionInterface;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use WHMCS\User\Client;

class TaxExempt extends Switcher implements \ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface, AjaxOnActionInterface
{
    protected string $provider = \ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\Providers\TaxExempt::class;
    protected string $providerAction = CrudProvider::ACTION_UPDATE;
}
