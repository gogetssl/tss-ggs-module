<?php

namespace ModulesGarden\TTSGGSModule\Components\BlockZeroData;

use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\DescriptionTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TitleTrait;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ComponentContainerInterface;

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
