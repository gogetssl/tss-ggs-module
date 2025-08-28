<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\BasicElements\LabelsBadgesButtons;

use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;

class LabelsBadgesButtons extends \ModulesGarden\TTSGGSModule\Components\Container\Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new Labels());
        $this->addElement(new Badges());
        $this->addElement(new Buttons());
        $this->addElement(new IconButtons());
        $this->addElement(new IconText());
        $this->addElement(new IconsAll());
        $this->addElement(new BurgerButtons());
    }
}
