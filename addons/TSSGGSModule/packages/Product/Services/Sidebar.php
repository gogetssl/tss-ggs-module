<?php

namespace ModulesGarden\TSSGGSModule\Packages\Product\Services;

use ModulesGarden\TSSGGSModule\Core\ModuleConstants;
use ModulesGarden\TSSGGSModule\Core\Services\Sidebar as CoreSidebarService;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TSSGGSModule\Core\UI\Sidebar\Item as SidebarItem;
use ModulesGarden\TSSGGSModule\Core\UI\Sidebar\Sidebar as SidebarContainer;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Hosting;
use ModulesGarden\TSSGGSModule\Packages\Product\Enums\ConfigSettings;

class Sidebar extends CoreSidebarService
{
    protected function load()
    {
        if (Request::get('action', '') != 'productdetails')
        {
            return;
        }

        $service = Hosting::find(Request::get('id'));

        if ($service->product->servertype != ModuleConstants::getModuleName())
        {
            return;
        }

        if ($service->domainstatus != 'Active')
        {
            return;
        }

        $this->build(\ModulesGarden\TSSGGSModule\Core\Support\Facades\Config::get(ConfigSettings::SIDEBARS, []));
    }

    protected function build(array $data = [])
    {
        $currentPage = Request::get('mg-page');

        foreach ($data as $sidebarName => $sidebarContent)
        {
            $sidebar = new SidebarContainer($sidebarName);

            foreach ($sidebarContent as $itemName => $item)
            {
                $itemUrl = is_callable($item['uri']) ? call_user_func($item['uri']) : $item['uri'];
                $sidebarItem = new SidebarItem($itemName, $itemUrl, $item['order']);

                $sidebarItem->setActive(rtrim($itemName, 's') == $currentPage);

                $sidebar->addItem($sidebarItem);
            }

            $this->addSidebar($sidebar);
        }
    }
}