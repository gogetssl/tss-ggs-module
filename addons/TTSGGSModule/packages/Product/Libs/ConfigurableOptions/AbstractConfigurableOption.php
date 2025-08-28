<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\Libs\ConfigurableOptions;

use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Product;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\ProductConfigOption;
use ModulesGarden\TTSGGSModule\Packages\Product\Libs\ConfigurableOptions\SubOption\SubOption;

/**
 *
 */
class AbstractConfigurableOption
{
    /**
     * @var int
     */
    protected int $gid = 0;
    /**
     * @var string
     */
    protected string $name;
    /**
     * @var int
     */
    protected int $type;
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
     * @var int
     */
    protected int $min = 0;
    /**
     * @var int
     */
    protected int $max = 0;
    /**
     * @var array
     */
    protected array $options = [];

    protected $loader = null;

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
     * @param Product $product
     * @return ProductConfigOption
     */
    public function create(Product $product): ProductConfigOption
    {
        $configOption = ProductConfigOption::create([
            'gid'        => $this->getGroupId(),
            'optionname' => $this->getFullName(),
            'optiontype' => $this->getType(),
            'qtyminimum' => $this->getMin(),
            'qtymaximum' => $this->getMax(),
            'order'      => $this->getOrder(),
            'hidden'     => $this->isHidden()
        ]);

        if (is_callable($this->loader))
        {
            ($this->loader)($this, $product);
        }

        foreach ($this->options as $subOption)
        {
            /**
             * @var $subOption SubOption
             */
            $newSubOption = $subOption->create($configOption);
            $subOption->generateDefaultPricing($newSubOption->id);
        }

        return $configOption;
    }

    public function getGroupId(): int
    {
        return $this->gid;
    }

    public function getFullName(): string
    {
        return $this->friendlyName ? $this->name . '|' . $this->friendlyName : $this->name;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getMin(): ?int
    {
        return $this->min;
    }

    public function getMax(): int
    {
        return $this->max;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setOptions(array $options): self
    {
        foreach ($options as $option)
        {
            $this->addOption($option);
        }

        return $this;
    }

    public function addOption(SubOption $option): self
    {
        $this->options[] = $option;

        return $this;
    }

    public function setOptionsLoader(callable $loader): self
    {
        $this->loader = $loader;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFriendlyName(): string
    {
        return $this->friendlyName;
    }

    public function setGroupId($gid)
    {
        return $this->gid = $gid;
    }

}