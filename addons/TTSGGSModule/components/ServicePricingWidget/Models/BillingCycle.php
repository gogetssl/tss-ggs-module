<?php

namespace ModulesGarden\TTSGGSModule\Components\ServicePricingWidget\Models;

use Illuminate\Contracts\Support\Arrayable;

class BillingCycle implements Arrayable
{
    protected string $billingCycle;
    protected array $additionalParams;
    protected Dropdown $dropdownInput;
    protected Number $priceInput;
    protected PriceLabel $primaryPriceLabel;
    protected PriceLabel $secondaryPriceLabel;

    public function __construct(
        string $billingCycle,
        Dropdown   $dropdownInput       = new Dropdown(),
        Number     $priceInput          = new Number(),
        PriceLabel $primaryPriceLabel   = new PriceLabel(),
        PriceLabel $secondaryPriceLabel = new PriceLabel(),
        array $additionalParams = [])
    {
        $this->billingCycle         = $billingCycle;
        $this->dropdownInput        = $dropdownInput;
        $this->priceInput           = $priceInput;
        $this->primaryPriceLabel    = $primaryPriceLabel;
        $this->secondaryPriceLabel  = $secondaryPriceLabel;
        $this->additionalParams     = $additionalParams;
    }

    public function getBillingCycle():string
    {
        return $this->billingCycle;
    }

    public function setDropdown(Dropdown $dropdown): self
    {
        $this->dropdownInput = $dropdown;

        return $this;
    }

    public function setPriceInput(Number $priceInput): self
    {
        $this->priceInput = $priceInput;

        return $this;
    }

    public function setPrimaryPriceLabel(PriceLabel $priceLabel): self
    {
        $this->primaryPriceLabel = $priceLabel;

        return $this;
    }

    public function setSecondaryPriceLabel(PriceLabel $priceLabel): self
    {
        $this->secondaryPriceLabel = $priceLabel;

        return $this;
    }

    public function toArray():array
    {
        return [
            'dropdownInput'         => $this->dropdownInput->toArray(),
            'priceInput'            => $this->priceInput->toArray(),
            'primaryPriceLabel'     => $this->primaryPriceLabel->toArray(),
            'secondaryPriceLabel'   => $this->secondaryPriceLabel->toArray(),
            'params'                => $this->additionalParams,
        ];
    }
}