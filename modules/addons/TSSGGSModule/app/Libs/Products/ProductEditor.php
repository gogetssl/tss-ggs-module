<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Products;

use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TSSGGSModule\App\Libs\Products\ConfigOptions\ConfigurableOptions;

/**
 * The "Product Configuration" dialog of the Products page: what an imported product is sold as in WHMCS (name,
 * description, auto setup, payment type, hidden), its SAN options and the module's own settings of the product
 * (certificate term, margin, processing guide, issued message - ProductSettings).
 */
class ProductEditor
{
    /** the longest WHMCS billing cycle (Triennially) */
    const RECURRING_MAX_MONTHS = 36;

    const PAY_TYPES  = ['recurring', 'onetime', 'free'];
    const AUTO_SETUP = ['order', 'payment', 'on', 'off'];

    /** SAN options of a product: form field that switches the option on => name of the configurable option */
    const SAN_OPTIONS = ['enableSan' => 'sans', 'enableWildcard' => 'sans_wildcard'];

    /**
     * @param int $id ID of the synchronized vendor product (RemoteProduct)
     * @return array|null the values of the dialog; null when the product is not imported
     */
    public static function load(int $id): ?array
    {
        $remoteProduct = RemoteProduct::find($id);
        $whmcsProduct  = $remoteProduct ? $remoteProduct->getWhmcsProduct() : null;
        if(!$whmcsProduct)
        {
            return null;
        }
        $configuration = (new ProductRepository())->getProductConfiguration($whmcsProduct->id);
        $raw           = (array)$remoteProduct->rawData;

        $data = [
            'id'                => $id,
            'whmcsProductId'    => (int)$whmcsProduct->id,
            'remoteProductName' => (string)$remoteProduct->name,
            'productName'       => (string)$whmcsProduct->name,
            'description'       => (string)$whmcsProduct->description,
            'autoSetup'         => $whmcsProduct->autosetup ?: 'off',
            'includedSan'       => (int)$configuration['included_san'],
            'includedWildcard'  => (int)$configuration['included_san_wildcard'],
            'hidden'            => (bool)$whmcsProduct->hidden,
            'payType'           => $whmcsProduct->paytype ?: 'recurring',
            // the most SANs that can be included in the price
            'maxIncluded'       => (int)($raw['san']['max'] ?? 0) + (int)($raw['san']['min'] ?? 0),
            'hasSan'            => false,
            'hasWildcard'       => false,
        ];

        $options = new ConfigurableOptions($whmcsProduct);
        foreach(self::SAN_OPTIONS as $field => $optionName)
        {
            $option                                               = $options->getConfigurableOptionByName($optionName);
            $data[$field]                                         = $option ? !(bool)$option->hidden : false;
            $data[$field === 'enableSan' ? 'hasSan' : 'hasWildcard'] = (bool)$option;
        }

        foreach(ProductSettings::get($whmcsProduct->id) as $key => $value)
        {
            $data[$key] = $value;
        }

        // the terms the product is sold for, and the saved one when the vendor no longer lists it
        $terms = self::productTerms($remoteProduct);
        $saved = (int)($data['months'] ?? 0);
        if($saved && !in_array($saved, $terms, true))
        {
            $terms[] = $saved;
        }
        $data['terms'] = $terms;

        return $data;
    }

    /**
     * @param array $form the dialog's values as posted (WHMCS hands request data HTML-encoded)
     * @throws \RuntimeException "notImported"; "monthsRecurringTooLong" when a recurring product gets a term over 36 months
     */
    public static function save(int $id, array $form): void
    {
        $remoteProduct = RemoteProduct::find($id);
        $whmcsProduct  = $remoteProduct ? $remoteProduct->getWhmcsProduct() : null;
        if(!$whmcsProduct)
        {
            throw new \RuntimeException('notImported');
        }
        $remoteProductData = (array)$remoteProduct->rawData;
        $productRepository = new ProductRepository();

        // a recurring product is billed at most every 36 months: a longer certificate term needs a one-time payment
        // (checked before anything is saved)
        $payType = in_array($form['payType'] ?? '', self::PAY_TYPES, true) ? $form['payType'] : $whmcsProduct->paytype;
        if((int)($form['months'] ?? 0) > self::RECURRING_MAX_MONTHS && $payType === 'recurring')
        {
            throw new \RuntimeException('monthsRecurringTooLong');
        }

        // ' arrives as &#039;: stored as typed, or every save would encode it again
        $decode = function($value) {
            return html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        };
        $autoSetup                 = in_array($form['autoSetup'] ?? '', self::AUTO_SETUP, true) ? $form['autoSetup'] : 'off';
        $whmcsProduct->name        = $decode($form['productName'] ?? $whmcsProduct->name);
        $whmcsProduct->description = $decode($form['description'] ?? '');
        $whmcsProduct->autosetup   = $autoSetup === 'off' ? '' : $autoSetup;
        $whmcsProduct->hidden      = !empty($form['hidden']) ? 1 : 0;
        if(in_array($form['payType'] ?? '', self::PAY_TYPES, true))
        {
            $whmcsProduct->paytype = $form['payType'];
        }
        $whmcsProduct->save();

        ProductSettings::save($whmcsProduct->id, [
            'months'          => $form['months'] ?? 0,
            'margin'          => $form['margin'] ?? '',
            'processingGuide' => $form['processingGuide'] ?? '',
            'issuedMessage'   => $form['issuedMessage'] ?? '',
        ]);

        // the fields are shown only for products with the SAN options: a value that was not sent stays as it is
        $data = [];
        if(isset($form['includedSan']))
        {
            $data['included_san'] = (int)$form['includedSan'];
        }
        if(isset($form['includedWildcard']))
        {
            $data['included_san_wildcard'] = (int)$form['includedWildcard'];
        }
        $productRepository->updateProductConfiguration($whmcsProduct->id, $data);

        $options = new ConfigurableOptions($whmcsProduct);
        foreach(self::SAN_OPTIONS as $field => $optionName)
        {
            $option = $options->getConfigurableOptionByName($optionName);
            if(!$option)
            {
                continue;
            }
            $hide     = !empty($form[$field]) ? '' : '1';
            $min      = $remoteProductData['san']['min'] ?? 0 ?: 0;
            $max      = $remoteProductData['san']['max'] ?? 0 ?: 0;
            $included = (int)($form[$field === 'enableSan' ? 'includedSan' : 'includedWildcard'] ?? 0);
            // what is included in the price is not sold again as an option
            $max = $max - ($included - $min);
            if($max <= 0)
            {
                $max  = 0;
                $hide = '1';
            }
            $option->hidden     = $hide;
            $option->qtyminimum = 0;
            $option->qtymaximum = $max;
            $option->save();
        }
    }

    /** @return int[] the certificate terms (months) the vendor sells the product for; all module terms when the catalogue has none */
    public static function productTerms($remoteProduct): array
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
}
