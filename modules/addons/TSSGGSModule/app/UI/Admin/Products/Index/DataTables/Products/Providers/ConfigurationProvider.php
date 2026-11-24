<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Providers;

use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Libs\ProductSettings;
use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Packages\Product\Services\ConfigurableOptions;


class ConfigurationProvider extends CrudProvider
{
    /** the longest WHMCS billing cycle (Triennially) */
    const RECURRING_MAX_MONTHS = 36;

    /** the certificate terms (months) the vendor sells the product for; all module terms when the catalogue has none */
    protected static function productTerms($remoteProduct): array
    {
        $terms = [];
        foreach(($remoteProduct && is_array($remoteProduct->rawData['prices'] ?? null)) ? $remoteProduct->rawData['prices'] : [] as $price)
        {
            $term = (int)($price['term'] ?? 0);
            if(in_array($term, ProductSettings::MONTHS, true) && $term > 0)
            {
                $terms[] = $term;
            }
        }
        $terms = $terms ? array_values(array_unique($terms)) : array_values(array_filter(ProductSettings::MONTHS));
        sort($terms);

        return $terms;
    }

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

        $this->data['payType'] = $whmcsProduct->paytype ?: 'recurring';
        foreach(ProductSettings::get($whmcsProduct->id) as $key => $value)
        {
            $this->data[$key] = $value;
        }

        $this->availableValues['payType'] = [
            'recurring' => $this->translate('payTypeRecurring'),
            'onetime'   => $this->translate('payTypeOnetime'),
            'free'      => $this->translate('payTypeFree'),
        ];
        // the terms the product is sold for; WHMCS bills up to 36 months (Triennially), so longer terms only fit a
        // one-time payment (availableValues is an ArrayAccess container: the options are built first, then set at once)
        $terms = [0 => $this->translate('monthsBillingCycle')];
        foreach(self::productTerms($remoteProduct) as $months)
        {
            $terms[$months] = $this->translate($months > self::RECURRING_MAX_MONTHS ? 'monthsValueOneTime' : 'monthsValue', ['months' => $months]);
        }
        $saved = (int)($this->data['months'] ?? 0);
        if($saved && !isset($terms[$saved]))
        {
            $terms[$saved] = $this->translate('monthsValue', ['months' => $saved]);
        }
        $this->availableValues['months'] = $terms;

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

        // a recurring product is billed at most every 36 months: a longer certificate term needs a one-time payment
        // (checked before anything is saved)
        $payType = in_array($this->formData['payType'], ['recurring', 'onetime', 'free'], true) ? $this->formData['payType'] : $whmcsProduct->paytype;
        if((int)$this->formData['months'] > self::RECURRING_MAX_MONTHS && $payType === 'recurring')
        {
            throw new \Exception($this->translate('monthsRecurringTooLong', ['max' => self::RECURRING_MAX_MONTHS]));
        }

        // WHMCS hands request data HTML-encoded (' arrives as &#039;): stored as typed, or every save would encode it again
        $decode                    = function($value) { return html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8'); };
        $whmcsProduct->name        = $decode($this->formData['productName']);
        $whmcsProduct->description = $decode($this->formData['description']);
        $whmcsProduct->autosetup   = ($this->formData['autoSetup'] == 'off') ? '' : $this->formData['autoSetup'];
        $whmcsProduct->hidden      = $this->formData['hidden'] ? 1 : 0;
        if(in_array($this->formData['payType'], ['recurring', 'onetime', 'free'], true))
        {
            $whmcsProduct->paytype = $this->formData['payType'];
        }
        $whmcsProduct->save();

        ProductSettings::save($whmcsProduct->id, [
            'months'          => $this->formData['months'],
            'margin'          => $this->formData['margin'],
            'processingGuide' => $this->formData['processingGuide'],
            'issuedMessage'   => $this->formData['issuedMessage'],
        ]);

        // the fields are shown only for products with the SAN options: a value that was not sent stays as it is
        $data = [];
        if(isset($this->formData['includedSan']))
        {
            $data['included_san'] = (int)$this->formData['includedSan'];
        }
        if(isset($this->formData['includedWildcard']))
        {
            $data['included_san_wildcard'] = (int)$this->formData['includedWildcard'];
        }

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
                $min = 0;

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