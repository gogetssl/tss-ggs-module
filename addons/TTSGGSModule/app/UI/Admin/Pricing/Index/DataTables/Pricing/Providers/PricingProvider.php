<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Pricing\Index\DataTables\Pricing\Providers;

use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TTSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Currency;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Pricing;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Product;

class PricingProvider extends CrudProvider
{
    public function read()
    {
        $this->data     = $this->formData;
        $whmcsProductId = $this->formData['whmcsProductId'];
        $currencies     = Currency::get();

        $productRepository                = new ProductRepository();
        $productConfiguration             = $productRepository->getProductConfiguration($whmcsProductId);
        $this->data['auto_update_enable'] = ($productConfiguration['price_auto'] == 'on');

        foreach($currencies as $currency)
        {
            $pricing = Pricing::where('type', 'product')
                              ->where('relid', $whmcsProductId)
                              ->where('currency', $currency->id)
                              ->first();

            $configurableOptionsData = Helpers::getConfigurableOptionsData($whmcsProductId, $currency->id);

            $expectedBillingCycles = [
                'annually',
                'biennially',
                'triennially'
            ];

            foreach($expectedBillingCycles as $billingCycle)
            {
                $price  = $pricing->{$billingCycle};
                $enable = 1;
                if(!$pricing || $price < 0)
                {
                    $price  = '0.00';
                    $enable = 0;
                }

                $this->data[$currency->code . '_' . $billingCycle]             = '"' . $price . '"';
                $this->data[$currency->code . '_' . $billingCycle . '_enable'] = $enable;

                foreach($configurableOptionsData as $configurableOptionData)
                {
                    $price = $configurableOptionData->{$billingCycle};
                    if(!$price || $price < 0)
                    {
                        $price = '0.00';
                    }

                    $this->data[$currency->code . '_' . $billingCycle . '_option_' . $configurableOptionData->subId] = '"' . $price . '"';
                }
            }
        }
    }

    public function update()
    {
        $whmcsProductId    = $this->formData['whmcsProductId'];
        $currencies        = Currency::get();
        $productRepository = new ProductRepository();

        $productRepository->updateProductConfiguration($whmcsProductId, [
            'price_auto' => ($this->formData['auto_update_enable'] ? 'on' : 'off'),
        ]);

        foreach($currencies as $currency)
        {
            $configurableOptionsData = Helpers::getConfigurableOptionsData($whmcsProductId, $currency->id);

            $expectedBillingCycles = [
                'monthly',
                'quarterly',
                'semiannually',
                'annually',
                'biennially',
                'triennially'
            ];

            foreach($expectedBillingCycles as $billingCycle)
            {
                $enable = (int)$this->formData[$currency->code . '_' . $billingCycle . '_enable'];

                if(isset($this->formData[$currency->code . '_' . $billingCycle]) && $enable)
                {
                    $price = floatval($this->formData[$currency->code . '_' . $billingCycle]);
                }
                else
                {
                    $price = -1;
                }

                Pricing::updateOrInsert(
                    ['type' => 'product', 'currency' => $currency->id, 'relid' => $whmcsProductId],
                    [$billingCycle => $price]
                );

                foreach($configurableOptionsData as $configurableOptionData)
                {
                    $subOptionId = $configurableOptionData->subId;
                    $price       = floatval($this->formData[$currency->code . '_' . $billingCycle . '_option_' . $subOptionId]);

                    Pricing::updateOrInsert(
                        ['type' => 'configoptions', 'currency' => $currency->id, 'relid' => $subOptionId],
                        [$billingCycle => $price]
                    );
                }
            }
        }
    }
}