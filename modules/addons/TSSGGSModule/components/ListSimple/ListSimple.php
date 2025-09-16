<?php

namespace ModulesGarden\TSSGGSModule\Components\ListSimple;

use ModulesGarden\TSSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\CssContainerTrait;

class ListSimple extends AbstractComponent
{
    use ComponentsContainerTrait;
    use CssContainerTrait;

    public const COMPONENT = 'ListSimple';

    public function addItem($item): self
    {
        $this->addElement($item);

        return $this;
    }
}
