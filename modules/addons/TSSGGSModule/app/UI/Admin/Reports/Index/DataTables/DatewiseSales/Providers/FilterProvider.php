<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\DatewiseSales\Providers;

use ModulesGarden\TSSGGSModule\App\Libs\Support\Helpers;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;

class FilterProvider extends CrudProvider
{
    public function read()
    {
        $this->data        = $this->formData;
        $productOptions    = Helpers::getProductOptions();
        $productOptions[0] = $this->translate('all');

        $this->availableValues['productId'] = $productOptions;
        $this->availableValues['brand']     = array_merge(['0' => $this->translate('all')], Helpers::getBrandOptions());

        $this->availableValues['sslStatus'] = [
            '0'         => $this->translate('all'),
            'active'    => $this->translate('active'),
            'pending'   => $this->translate('pending'),
            'expired'   => $this->translate('expired'),
            'cancelled' => $this->translate('cancelled'),
        ];
    }
}
