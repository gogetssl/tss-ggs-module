<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\Libs\ConfigurableOptions\SubOption;

use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Currency;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Pricing;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\ProductConfigOption;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\ProductConfigOptionSub;

/**
 *
 */
class SubOption
{
    /**
     * @var string
     */
    protected string $name;
    /**
     * @var string
     */
    protected string $friendlyName = '';
    /**
     * @var int
     */
    protected int $order = 0;
    /**
     * @var bool
     */
    protected bool $hidden;

    /**
     * @param string $name
     * @param string $friendlyName
     * @param int $order
     * @param bool $hidden
     */
    public function __construct(string $name, string $friendlyName = '', int $order = 0, bool $hidden = false)
    {
        $this->name         = $name;
        $this->friendlyName = $friendlyName;
        $this->order        = $order;
        $this->hidden       = $hidden;
    }

    /**
     * @param ProductConfigOption $configOption
     * @return ProductConfigOptionSub
     */
    public function create(ProductConfigOption $configOption): ProductConfigOptionSub
    {
        return ProductConfigOptionSub::create([
            'configid'   => $configOption->id,
            'optionname' => $this->friendlyName ? $this->name . '|' . $this->friendlyName : $this->name,
            'sortorder'  => $this->order,
            'hidden'     => $this->hidden
        ]);
    }

    public function generateDefaultPricing(int $relId = null, string $defaultPrice = '0.00')
    {
        $currencyIds = Currency::pluck("id")->all();

        if (empty($currencyIds) || !$relId)
        {
            return null;
        }

        foreach ( $currencyIds as $currencyId)
        {
            $pricing = new Pricing();

            $insertData = [];

            $insertData['type'] = 'configoptions';
            $insertData['currency'] = $currencyId;
            $insertData['relid'] = $relId;

            foreach ($pricing->priceFields() as $cycle)
            {
                $insertData[$cycle] = $defaultPrice;
            }

            foreach ($pricing->setupFields() as $setup)
            {
                $insertData[$setup] = $defaultPrice;
            }

            Pricing::create($insertData);
        }
    }
}