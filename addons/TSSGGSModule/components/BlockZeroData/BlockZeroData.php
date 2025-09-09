<?php

namespace ModulesGarden\TSSGGSModule\Components\BlockZeroData;

use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\DescriptionTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\TitleTrait;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ComponentContainerInterface;

class BlockZeroData extends Container implements ComponentContainerInterface
{
    use TitleTrait;
    use DescriptionTrait;

    public const COMPONENT = 'BlockZeroData';

    public function setIcon(string $icon): self
    {
        $this->setSlot('icon', $icon);

        return $this;
    }
}
