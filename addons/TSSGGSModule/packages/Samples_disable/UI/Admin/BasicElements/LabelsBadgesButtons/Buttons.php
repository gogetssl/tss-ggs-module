<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\BasicElements\LabelsBadgesButtons;

use ModulesGarden\TSSGGSModule\Components\Button\ButtonDanger;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonInfo;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonWarning;
use ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;


class Buttons extends Widget implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setTitle('Buttons');

        $bar = new Toolbar();

        $bar->addElement((new ButtonSuccess())->setTitle('Button Success'));
        $bar->addElement((new ButtonDanger())->setTitle('Button Danger'));
        $bar->addElement((new ButtonWarning())->setTitle('Button Warning'));
        $bar->addElement((new ButtonInfo())->setTitle('Button Info'));
        $bar->addElement((new ButtonPrimary())->setTitle('Button Primary'));

        $bar->addElement((new ButtonSuccess())->setTitle('Button Success')->setIcon('plus'));
        $bar->addElement((new ButtonDanger())->setTitle('Button Danger')->setIcon('plus'));
        $bar->addElement((new ButtonWarning())->setTitle('Button Warning')->setIcon('plus'));
        $bar->addElement((new ButtonInfo())->setTitle('Button Info')->setIcon('plus'));
        $bar->addElement((new ButtonPrimary())->setTitle('Button Primary')->setIcon('plus'));

        $this->addElement($bar);
    }
}
