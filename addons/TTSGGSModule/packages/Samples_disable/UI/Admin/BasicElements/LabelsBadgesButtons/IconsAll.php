<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\BasicElements\LabelsBadgesButtons;

use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Components\IconButton\IconButton;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;


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
