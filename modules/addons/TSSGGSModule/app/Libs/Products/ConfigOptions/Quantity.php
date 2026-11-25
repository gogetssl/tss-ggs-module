<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Products\ConfigOptions;

/** A quantity option ("how many additional SANs"): setRange(0, 250); max 0 = no limit */
class Quantity extends ConfigurableOption
{
    protected $type = 4;

    public function setRange(int $min, int $max = 0): self
    {
        $this->min = $min;
        $this->max = $max;

        return $this;
    }
}
