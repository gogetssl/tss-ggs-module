<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Providers;

use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Pricing;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Product;
use ModulesGarden\TTSGGSModule\Packages\Product\Libs\ConfigurableOptions\Quantity;
use ModulesGarden\TTSGGSModule\Packages\Product\Libs\ConfigurableOptions\SubOption\SubOption;
use ModulesGarden\TTSGGSModule\Packages\Product\Services\ConfigurableOptions;

class ReSyncProvider extends CrudProvider
{
    public function read()
    {
        $this->data = $this->formData;
    }

    public function create()
    {
        Helpers::reSyncProducts();
    }
}