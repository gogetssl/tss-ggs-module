<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\BasicElements\LabelsBadgesButtons;

use ModulesGarden\TTSGGSModule\Components\Badge\BadgeDanger;
use ModulesGarden\TTSGGSModule\Components\Badge\BadgeInfo;
use ModulesGarden\TTSGGSModule\Components\Badge\BadgeSuccess;
use ModulesGarden\TTSGGSModule\Components\Badge\BadgeWarning;
use ModulesGarden\TTSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;


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
