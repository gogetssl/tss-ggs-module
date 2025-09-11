<?php

namespace ModulesGarden\TSSGGSModule\Components\Card;

use ModulesGarden\TSSGGSModule\Components\Button\Button;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Icon\Icon;
use ModulesGarden\TSSGGSModule\Core\Components\Enums\LayoutProps;

class CardButton extends Card
{
    /**
     * @param Button $button
     * @return $this
     * @deprecated - use addButton
     */
    public function setButton(Button $button): self
    {
        $this->addButton($button);

        return $this;
    }

    public function addButton($button): Card
    {
        $this->addComponent('actions', $button->setLayoutProp(LayoutProps::FULL_WIDTH));

        return $this;
    }

    public function setIcon(string $icon): self
    {
        $iconContainer = new Container();
        //$iconContainer->setBorder(BorderColors::DANGER, BorderWidths::WIDTH_2);
        //$iconContainer->setBorderCircled();
        $iconContainer->addElement((new Icon())->setIcon($icon)->setContentCentered());
        $this->addToRightSidebar($iconContainer);

        return $this;
    }
}