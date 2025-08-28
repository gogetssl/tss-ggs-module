<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\BasicElements\LabelsBadgesButtons;

use ModulesGarden\TTSGGSModule\Components\DropdownMenu\DropdownMenu;
use ModulesGarden\TTSGGSModule\Components\DropdownMenuItem\DropdownMenuItem;
use ModulesGarden\TTSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use function ModulesGarden\TTSGGSModule\Core\translator;

class BurgerButtons extends Widget implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setTitle(translator()->get('Icon Buttons'));

        $dropdown = (new DropdownMenu());
        $dropdown->addItem((new DropdownMenuItem())
            ->setIcon('delete')
            ->setTitle('Delete'));

        $dropdown->addItem((new DropdownMenuItem())
            ->setIcon('plus')
            ->setTitle('Add'));

        $bar = new Toolbar();
        $bar->addElement($dropdown);

        $this->addElement($bar);
    }
}
