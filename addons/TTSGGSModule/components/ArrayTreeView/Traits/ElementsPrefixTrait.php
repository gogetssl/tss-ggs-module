<?php

namespace ModulesGarden\TTSGGSModule\Components\ArrayTreeView\Traits;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;

trait ElementsPrefixTrait
{
    protected string|AbstractComponent|null $elementsPrefix = null;

    public function setElementsPrefix($prefix):self
    {
        $this->elementsPrefix = $prefix;

        return $this;
    }

    public function elementsPrefixSlotBuilder():string|AbstractComponent|null
    {
        return $this->elementsPrefix;
    }

}