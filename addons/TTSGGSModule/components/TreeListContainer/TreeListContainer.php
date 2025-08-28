<?php

namespace ModulesGarden\TTSGGSModule\Components\TreeListContainer;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\AjaxTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ComponentsContainerTrait;

/**
 * Class PreBlock
 */
class TreeListContainer extends AbstractComponent
{
    use AjaxTrait;
    use ComponentsContainerTrait;

    public const COMPONENT = 'TreeListContainer';

    public function openOnActiveItems(bool $openOnActiveItems = true): self
    {
        $this->setSlot('openOnActiveItems', $openOnActiveItems);

        return $this;
    }
}
