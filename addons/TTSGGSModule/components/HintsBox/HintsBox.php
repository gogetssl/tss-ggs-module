<?php

namespace ModulesGarden\TTSGGSModule\Components\HintsBox;

use ModulesGarden\TTSGGSModule\Components\Hint\Hint;
use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\CssContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TitleTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ToolbarTrait;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;

class HintsBox extends AbstractComponent implements AdminAreaInterface
{
    use TitleTrait;
    use ToolbarTrait;
    use ComponentsContainerTrait;
    use CssContainerTrait;

    public const COMPONENT = 'HintsBox';

    public function addHint(Hint $hint): self
    {
        $this->addComponent('hints', $hint);

        return $this;
    }
}