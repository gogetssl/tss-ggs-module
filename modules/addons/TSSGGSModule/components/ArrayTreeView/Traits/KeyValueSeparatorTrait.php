<?php

namespace ModulesGarden\TSSGGSModule\Components\ArrayTreeView\Traits;

use ModulesGarden\TSSGGSModule\Core\Components\AbstractComponent;

trait KeyValueSeparatorTrait
{
    protected string|AbstractComponent|null $keyValueSeparator = null;

    public function setKeyValueSeparator($separator):self
    {
        $this->keyValueSeparator = $separator;

        return $this;
    }

    public function keyValueSeparatorSlotBuilder(): string|AbstractComponent|null
    {
        return $this->keyValueSeparator;
    }
}