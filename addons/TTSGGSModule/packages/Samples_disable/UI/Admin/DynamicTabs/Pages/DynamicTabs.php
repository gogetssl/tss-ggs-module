<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DynamicTabs\Pages;

use ModulesGarden\TTSGGSModule\Components\MarkdownEditor\MarkdownEditor;
use ModulesGarden\TTSGGSModule\Components\Tab\Tab;
use ModulesGarden\TTSGGSModule\Components\TabsWidget\TabsWidget;
use ModulesGarden\TTSGGSModule\Components\IconButton\IconButton;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ModalLoad;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Packages\ModuleSettings\Support\Facades\ModuleSettings;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DynamicTabs\Modals\EditTabsModal;

class DynamicTabs extends TabsWidget implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->setTitle("Dynamic Tabs Sample");

        $tabsNames = json_decode(ModuleSettings::get('samples.dynamicTabsNames')) ?: [];

        asort($tabsNames);

        array_unshift($tabsNames, "Default Tab");

        foreach ($tabsNames as $tabsName)
        {
            $tab = new Tab();
            $tab->setTitle($tabsName);
            $tab->addElement(new MarkdownEditor());
            $this->addTab($tab);
        }

        $this->addToToolbar((new IconButton())
            ->setTitle("Edit")
            ->setIcon("plus")
            ->onClick(new ModalLoad(new EditTabsModal)));
    }
}