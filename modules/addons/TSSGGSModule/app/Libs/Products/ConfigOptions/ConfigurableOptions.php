<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Products\ConfigOptions;

use ModulesGarden\TSSGGSModule\App\Models\Whmcs\Product;
use ModulesGarden\TSSGGSModule\App\Models\Whmcs\ProductConfigOption;

/**
 * The configurable options of a WHMCS product (the SAN counts of a certificate product):
 *
 *   $options = new ConfigurableOptions($product);
 *   $options->createConfigurableOption((new Quantity('sans', 'Additional SANs'))->setRange(0, 250)->addOption(new SubOption('SAN')));
 *   $option = $options->getConfigurableOptionByName('sans');
 *
 * An option is known by the part of its name before "|" ("sans|Additional SANs" is "sans").
 */
class ConfigurableOptions
{
    protected $configOptions = null;
    protected $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /** @return ProductConfigOption|false false when the product has the option already */
    public function createConfigurableOption(ConfigurableOption $configurableOption)
    {
        if($this->getConfigurableOptionByName($configurableOption->getName()) !== null)
        {
            return false;
        }
        $group = (new ConfigurableOptionsGroup())->getFirstOrCreateRelated($this->product);
        $configurableOption->setGroupId((int)$group->id);

        return $configurableOption->create($this->product);
    }

    /** @return ProductConfigOption|null */
    public function getConfigurableOptionByName(string $optionName)
    {
        if(trim($optionName) === '')
        {
            return null;
        }
        if($this->configOptions === null)
        {
            $this->configOptions = ProductConfigOption::ofProductId($this->product->id)->get();
        }
        foreach($this->configOptions as $option)
        {
            if(self::rawName($optionName) === self::rawName($option->optionname))
            {
                return $option;
            }
        }

        return null;
    }

    protected static function rawName($optionName): string
    {
        return explode('|', (string)$optionName)[0];
    }
}
