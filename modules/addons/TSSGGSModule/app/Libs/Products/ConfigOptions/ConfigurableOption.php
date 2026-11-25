<?php

namespace TSSGGSModule\App\Libs\Products\ConfigOptions;

use TSSGGSModule\App\Models\Whmcs\Product;
use TSSGGSModule\App\Models\Whmcs\ProductConfigOption;

/**
 * A configurable option to create for a WHMCS product: its name ("sans"), the name the client sees, its type
 * (WHMCS: 1 dropdown, 2 radio, 3 yes/no, 4 quantity), the range of a quantity and its sub-options.
 * The module only creates quantities (Quantity).
 */
class ConfigurableOption
{
    protected $gid = 0;
    protected $name;
    protected $type = 1;
    protected $friendlyName;
    protected $order;
    protected $hidden;
    protected $min = 0;
    protected $max = 0;
    /** @var SubOption[] */
    protected $options = [];

    public function __construct(string $name, string $friendlyName = '', int $order = 0, bool $hidden = false)
    {
        $this->name         = $name;
        $this->friendlyName = $friendlyName;
        $this->order        = $order;
        $this->hidden       = $hidden;
    }

    /** Creates the option in the group set with setGroupId(), with its sub-options and their prices (0.00) */
    public function create(Product $product): ProductConfigOption
    {
        $configOption = ProductConfigOption::create([
            'gid'        => $this->gid,
            'optionname' => $this->friendlyName ? $this->name . '|' . $this->friendlyName : $this->name,
            'optiontype' => $this->type,
            'qtyminimum' => $this->min,
            'qtymaximum' => $this->max,
            'order'      => $this->order,
            'hidden'     => $this->hidden,
        ]);
        foreach($this->options as $subOption)
        {
            $created = $subOption->create($configOption);
            $subOption->generateDefaultPricing((int)$created->id);
        }

        return $configOption;
    }

    public function addOption(SubOption $option): self
    {
        $this->options[] = $option;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setGroupId(int $gid): self
    {
        $this->gid = $gid;

        return $this;
    }
}
