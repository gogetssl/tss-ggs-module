<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DynamicTabs\Pages;

use ModulesGarden\TSSGGSModule\Components\MarkdownEditor\MarkdownEditor;
use ModulesGarden\TSSGGSModule\Components\Tab\Tab;
use ModulesGarden\TSSGGSModule\Components\TabsWidget\TabsWidget;
use ModulesGarden\TSSGGSModule\Components\IconButton\IconButton;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalLoad;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Packages\ModuleSettings\Support\Facades\ModuleSettings;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DynamicTabs\Modals\EditTabsModal;

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