<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\BasicElements\LabelsBadgesButtons;

use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\IconButton\IconButton;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\ModuleConstants;


class IconsAll extends Widget implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setTitle('All Icons');

        $bar = new Container();

        preg_match_all('/\.mdi-([a-z]+)::before/', file_get_contents(ModuleConstants::getAssetsDir('assets', 'css', 'materialdesignicons.min.css')), $matches);

        foreach (array_unique($matches[1]) as $icon)
        {
            $bar->addElement((new IconButton())->setIcon($icon)->setTitle('lu-mdi mdi mdi-' . $icon));
        }

        $this->addElement($bar);
    }

}
