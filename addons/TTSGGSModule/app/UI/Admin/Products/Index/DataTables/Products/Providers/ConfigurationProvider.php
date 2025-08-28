<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Providers;

use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Packages\Product\Services\ConfigurableOptions;


class ConfigurationProvider extends CrudProvider
{
    public function read()
    {
        $this->data = $this->formData;

        $localId              = $this->formData['id'];
        $remoteProduct        = RemoteProduct::find($localId);
        $whmcsProduct         = $remoteProduct->getWhmcsProduct();
        $productRepository    = new ProductRepository();
        $productConfiguration = $productRepository->getProductConfiguration($whmcsProduct->id);

        $this->data['whmcsProductId']    = $whmcsProduct->id;
        $this->data['remoteProductName'] = $remoteProduct->name;
        $this->data['productName']       = $whmcsProduct->name;
        $this->data['description']       = $whmcsProduct->description;
        $this->data['autoSetup']         = $whmcsProduct->autosetup ?: 'off';
        $this->data['includedSan']       = (int)$productConfiguration['included_san'];
        $this->data['includedWildcard']  = (int)$productConfiguration['included_san_wildcard'];
        $this->data['hidden']            = (bool)$whmcsProduct->hidden;


        $expectedOptionNames = [
            'enableSan'      => 'sans',
            'enableWildcard' => 'sans_wildcard',
        ];

        $configurableOptionsService = new ConfigurableOptions($whmcsProduct);

        foreach($expectedOptionNames as $formKey => $localOptionName)
        {
            $configurableOptionModel = $configurableOptionsService->getConfigurableOptionByName($localOptionName);

            if($configurableOptionModel)
            {
                $this->data[$formKey] = !(bool)$configurableOptionModel->hidden;
            }
            else
            {
                $this->data[$formKey] = false;
            }
        }

        $this->availableValues['autoSetup'] =
            [
                'order'   => $this->translate('autoSetupOrder'),
                'payment' => $this->translate('autoSetupPayment'),
                'on'      => $this->translate('autoSetupOn'),
                'off'     => $this->translate('autoSetupOff'),
            ];
    }

    public function update()
    {
        $localId              = $this->formData['id'];
        $remoteProduct        = RemoteProduct::find($localId);
        $remoteProductData    = $remoteProduct->rawData;
        $whmcsProduct         = $remoteProduct->getWhmcsProduct();
        $productRepository    = new ProductRepository();
        $productConfiguration = $productRepository->getProductConfiguration($whmcsProduct->id);

        $whmcsProduct->name        = $this->formData['productName'];
        $whmcsProduct->description = $this->formData['description'];
        $whmcsProduct->autosetup   = ($this->formData['autoSetup'] == 'off') ? '' : $this->formData['autoSetup'];
        $whmcsProduct->hidden      = $this->formData['hidden'] ? 'on' : '';
        $whmcsProduct->save();

        $data = [
            'included_san'          => (int)$this->formData['includedSan'],
            'included_san_wildcard' => (int)$this->formData['includedWildcard'],
        ];

        $productRepository->updateProductConfiguration($whmcsProduct->id, $data);

        $expectedOptionNames = [
            'enableSan'      => 'sans',
            'enableWildcard' => 'sans_wildcard',
        ];

        $configurableOptionsService = new ConfigurableOptions($whmcsProduct);

        foreach($expectedOptionNames as $formKey => $localOptionName)
        {
            $configurableOptionModel = $configurableOptionsService->getConfigurableOptionByName($localOptionName);

            if($configurableOptionModel)
            {
                $hideOption = $this->formData[$formKey] ? '' : '1';
                $min        = $remoteProductData['san']['min'] ?: 0;
                $max        = $remoteProductData['san']['max'] ?: 0;
                $included   = 0;

                if($formKey == 'enableSan')
                {
                    $included = (int)$this->formData['includedSan'];
                }
                elseif($formKey == 'enableWildcard')
                {
                    $included = (int)$this->formData['includedWildcard'];
                }

                $max = $max - ($included - $min);
                $min = $min - $included;

                if($min < 0)
                {
                    $min = 0;
                }

                if($max <= 0)
                {
                    $max        = 0;
                    $hideOption = '1';
                }


                $configurableOptionModel->hidden     = $hideOption;
                $configurableOptionModel->qtyminimum = $min;
                $configurableOptionModel->qtymaximum = $max;
                $configurableOptionModel->save();
            }
        }
    }
}