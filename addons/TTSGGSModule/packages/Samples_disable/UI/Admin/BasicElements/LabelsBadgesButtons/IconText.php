<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\BasicElements\LabelsBadgesButtons;

use ModulesGarden\TTSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;


class IconText extends Widget implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setTitle('Icon Text');

        $bar = new Toolbar();

        $bar->addElement((new \ModulesGarden\TTSGGSModule\Components\IconText\IconText())->setIcon('plus')->setTitle('Plus'));

        $this->addElement($bar);
    }
}
