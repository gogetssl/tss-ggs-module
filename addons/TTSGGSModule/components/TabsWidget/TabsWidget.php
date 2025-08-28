<?php

namespace ModulesGarden\TTSGGSModule\Components\TabsWidget;

use ModulesGarden\TTSGGSModule\Components\Tab\Tab;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ComponentInterface;

/**
 * Class TabsWidget
 */
class TabsWidget extends Widget
{
    public const COMPONENT = 'TabsWidget';

    /**
     * @param ComponentInterface $component
     */
    public function addTab(Tab $component)
    {
        $this->addComponent('tabs', $component);
    }

    public function disableSwiper(bool $disableSwiper = true):self
    {
        $this->setSlot('disableSwiper', $disableSwiper);

        return $this;
    }
}
