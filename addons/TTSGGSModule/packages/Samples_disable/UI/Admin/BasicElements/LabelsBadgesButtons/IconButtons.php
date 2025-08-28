<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\BasicElements\LabelsBadgesButtons;

use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Components\IconButton\IconButtonDanger;
use ModulesGarden\TTSGGSModule\Components\IconButton\IconButtonEdit;
use ModulesGarden\TTSGGSModule\Components\IconButton\IconButtonInfo;
use ModulesGarden\TTSGGSModule\Components\IconButton\IconButtonPrimary;
use ModulesGarden\TTSGGSModule\Components\IconButton\IconButtonSuccess;
use ModulesGarden\TTSGGSModule\Components\IconButton\IconButtonWarning;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;


class IconButtons extends Widget implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setTitle('Icon Buttons');

        $bar = new Container();

        $bar->addElement((new IconButtonDanger())->setIcon('delete')->setTitle('IconButtonDanger'));
        $bar->addElement((new IconButtonInfo())->setIcon('shape')->setTitle('IconButtonInfo'));
        $bar->addElement((new IconButtonWarning())->setIcon('google')->setTitle('IconButtonWarning'));
        $bar->addElement((new IconButtonSuccess())->setIcon('abacus')->setTitle('IconButtonSuccess'));
        $bar->addElement((new IconButtonEdit())->setTitle('IconButtonEdit'));
        $bar->addElement((new IconButtonPrimary())->setIcon('airplane')->setTitle('IconButtonPrimary'));

        $this->addElement($bar);
    }

}
