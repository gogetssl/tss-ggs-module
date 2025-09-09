<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\Providers;

use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TSSGGSModule\Core\Components\Action;
use ModulesGarden\TSSGGSModule\Core\Components\Response\Response;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Currency;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Pricing;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Product;

class CurrencyProvider extends CrudProvider
{
    public function read()
    {
        $this->data = $this->formData;
    }

    public function update()
    {
        $currency = $this->formData['currency'];

        //Helpers::debugLog('CurrencyProvider', 'update', $_REQUEST);

        return (new Response())
            ->setActions([
                             Action::reloadById('PricingDataTable')->withParams(['currency' => $currency]),
                         ]);
    }
}