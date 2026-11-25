<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Products\ConfigOptions;

use ModulesGarden\TSSGGSModule\App\Models\Whmcs\Currency;
use ModulesGarden\TSSGGSModule\App\Models\Whmcs\Pricing;
use ModulesGarden\TSSGGSModule\App\Models\Whmcs\ProductConfigOption;
use ModulesGarden\TSSGGSModule\App\Models\Whmcs\ProductConfigOptionSub;

/** A sub-option of a configurable option (for a quantity: the unit the price is set on) */
class SubOption
{
    protected $name;
    protected $friendlyName;
    protected $order;
    protected $hidden;

    public function __construct(string $name, string $friendlyName = '', int $order = 0, bool $hidden = false)
    {
        $this->name         = $name;
        $this->friendlyName = $friendlyName;
        $this->order        = $order;
        $this->hidden       = $hidden;
    }

    public function create(ProductConfigOption $configOption): ProductConfigOptionSub
    {
        return ProductConfigOptionSub::create([
            'configid'   => $configOption->id,
            'optionname' => $this->friendlyName ? $this->name . '|' . $this->friendlyName : $this->name,
            'sortorder'  => $this->order,
            'hidden'     => $this->hidden,
        ]);
    }

    /** A pricing row per currency with every cycle and setup fee at $defaultPrice (the price sync fills them in) */
    public function generateDefaultPricing(int $relId, string $defaultPrice = '0.00'): void
    {
        if(!$relId)
        {
            return;
        }
        foreach(Currency::pluck('id')->all() as $currencyId)
        {
            $pricing = new Pricing();
            $row     = ['type' => 'configoptions', 'currency' => $currencyId, 'relid' => $relId];
            foreach(array_merge($pricing->priceFields(), $pricing->setupFields()) as $field)
            {
                $row[$field] = $defaultPrice;
            }
            Pricing::create($row);
        }
    }
}
