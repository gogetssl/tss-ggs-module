<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\Libs\ConfigurableOptions;

class Quantity extends AbstractConfigurableOption
{
    protected int $type = 4;

    /**
     * @param int $min
     * @param int $max
     * @return $this
     */
    public function setRange(int $min, int $max = 0): self
    {
        $this->min = $min;
        $this->max = $max;

        return $this;
    }
}