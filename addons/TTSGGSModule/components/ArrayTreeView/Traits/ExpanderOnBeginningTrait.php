<?php

namespace ModulesGarden\TTSGGSModule\Components\ArrayTreeView\Traits;

trait ExpanderOnBeginningTrait
{
    protected bool $expanderOnBeginning = false;

    public function enableExpanderOnBeginning(bool $expanderOnBeginning = true):self
    {
        $this->expanderOnBeginning = $expanderOnBeginning;

        return $this;
    }

    public function expanderOnBeginningSlotBuilder():bool
    {
        return $this->expanderOnBeginning;
    }

}