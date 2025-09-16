<?php

namespace ModulesGarden\TSSGGSModule\Components\BoardItem;

use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\BorderTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\TextTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\TitleTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ToolbarTrait;

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
