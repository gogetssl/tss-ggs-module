<?php

namespace ModulesGarden\TSSGGSModule\Components\HintsBox;

use ModulesGarden\TSSGGSModule\Components\Hint\Hint;
use ModulesGarden\TSSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\CssContainerTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\TitleTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ToolbarTrait;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;

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