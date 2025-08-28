<?php

namespace ModulesGarden\TTSGGSModule\Components\ServicePricingWidget\Models;

use Illuminate\Contracts\Support\Arrayable;

class DataSet implements Arrayable
{
    protected array $billingCycles = [];

    public function __construct(array $billingCycles = [])
    {
        foreach ($billingCycles as $billingCycle)
        {
            $this->add($billingCycle);
        }
    }

    public function add(BillingCycle $billingCycle):self
    {
        $this->billingCycles[$billingCycle->getBillingCycle()] = $billingCycle;

        return $this;
    }

    public function toArray():array
    {
        return array_map(function (BillingCycle $billingCycle) {
            return $billingCycle->toArray();
        }, $this->billingCycles);
    }
}