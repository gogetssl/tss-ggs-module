<?php

namespace ModulesGarden\TSSGGSModule\Components\TabsWidget;

use ModulesGarden\TSSGGSModule\Components\Tab\Tab;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ComponentInterface;

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
