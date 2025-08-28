<?php

namespace ModulesGarden\TTSGGSModule\Components\BoardItem;

use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\BorderTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TextTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TitleTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ToolbarTrait;

class BoardItem extends Container
{
    use TitleTrait;
    use TextTrait;
    use ToolbarTrait;
    use BorderTrait;

    public const COMPONENT = 'BoardItem';

    /**
     * @param string $subTitle
     * @return self
     **/
    public function setSubTitle(string $subTitle): self
    {
        $this->setSlot('subTitle', $subTitle);

        return $this;
    }

    public function addTopElement($element): self
    {
        $this->addTopComponent('elements', $element);

        return $this;
    }

    protected function addTopComponent($type, $element): self
    {
        $this->pushToSlot('topElements.' . $type, $element);

        return $this;
    }

    protected function topElementsSlotBuilder()
    {
        return $this->getSlot('topElements');
    }

}
