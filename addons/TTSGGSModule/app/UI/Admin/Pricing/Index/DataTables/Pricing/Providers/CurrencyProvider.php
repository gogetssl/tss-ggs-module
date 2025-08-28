<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\Providers;

use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TTSGGSModule\Core\Components\Action;
use ModulesGarden\TTSGGSModule\Core\Components\Response\Response;
use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Currency;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Pricing;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Product;

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