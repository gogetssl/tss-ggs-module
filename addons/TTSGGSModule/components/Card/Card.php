<?php

namespace ModulesGarden\TTSGGSModule\Components\Card;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\BorderTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ContentTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\CssContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TitleTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\DescriptionTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ToolbarTrait;

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