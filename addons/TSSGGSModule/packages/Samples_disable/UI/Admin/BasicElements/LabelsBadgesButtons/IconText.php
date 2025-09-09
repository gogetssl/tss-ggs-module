<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\BasicElements\LabelsBadgesButtons;

use ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;


class IconText extends Widget implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setTitle('Icon Text');

        $bar = new Toolbar();

        $bar->addElement((new \ModulesGarden\TSSGGSModule\Components\IconText\IconText())->setIcon('plus')->setTitle('Plus'));

        $this->addElement($bar);
    }
}
