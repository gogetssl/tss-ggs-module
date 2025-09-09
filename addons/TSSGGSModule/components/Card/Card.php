<?php

namespace ModulesGarden\TSSGGSModule\Components\Card;

use ModulesGarden\TSSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\BorderTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ContentTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\CssContainerTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\TitleTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\DescriptionTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ToolbarTrait;

class Card extends AbstractComponent
{
    use TitleTrait;
    use DescriptionTrait;
    use ContentTrait;
    use ToolbarTrait;
    use ComponentsContainerTrait;
    use CssContainerTrait;
    use BorderTrait;

    public const COMPONENT = 'Card';

    public function addToLeftSidebar($element): self
    {
        $this->addComponent('leftSidebar', $element);

        return $this;
    }

    public function addToRightSidebar($element): self
    {
        $this->addComponent('rightSidebar', $element);

        return $this;
    }
}