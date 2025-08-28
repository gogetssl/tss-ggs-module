<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\ProfitLoss\Providers;

use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;

class FilterProvider extends CrudProvider
{
    public function read()
    {
        $this->data = $this->formData;

        $this->availableValues['profitLossPeriod'] = [
            'last_30' => $this->translate('last30'),
            'last_60' => $this->translate('last60'),
            'last_90' => $this->translate('last90'),
            'next_30' => $this->translate('next30'),
            'next_60' => $this->translate('next60'),
            'next_90' => $this->translate('next90'),
        ];

    }
}