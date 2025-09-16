<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\BasicElements\LabelsBadgesButtons;

use ModulesGarden\TSSGGSModule\Components\Badge\BadgeDanger;
use ModulesGarden\TSSGGSModule\Components\Badge\BadgeInfo;
use ModulesGarden\TSSGGSModule\Components\Badge\BadgeSuccess;
use ModulesGarden\TSSGGSModule\Components\Badge\BadgeWarning;
use ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;


class Badges extends Widget implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setTitle('Badges');

        $bar = new Toolbar();

        $bar->addElement((new BadgeDanger())->setText('1'));
        $bar->addElement((new BadgeSuccess())->setText('2'));
        $bar->addElement((new BadgeWarning())->setText('3'));
        $bar->addElement((new BadgeInfo())->setText('4'));

        $this->addElement($bar);
    }
}
