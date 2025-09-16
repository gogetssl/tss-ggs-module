<?php

namespace ModulesGarden\TSSGGSModule\Components\TreeListItem;

use ModulesGarden\TSSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ActionOnClickTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\AjaxTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\TitleTrait;

/**
 * Class PreBlock
 */
class TreeListItem extends AbstractComponent
{
    use AjaxTrait;
    use TitleTrait;
    use ComponentsContainerTrait;
    use ActionOnClickTrait;

    public const COMPONENT = 'TreeListItem';

    public function setActive(bool $active = true): self
    {
        $this->setSlot('isActive', $active);

        return $this;
    }

    public function setOpen(bool $open = true): self
    {
        $this->setSlot('isOpen', $open);

        return $this;
    }
}
