<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Client\Sidebars;

use ModulesGarden\TTSGGSModule\App\UI\Client\Sidebars\Buttons\SidebarSampleButton;
use ModulesGarden\TTSGGSModule\Components\Sidebar\Sidebar;
use ModulesGarden\TTSGGSModule\Components\SidebarItem\SidebarItem;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ClientAreaInterface;

class MainSidebar extends Sidebar implements ClientAreaInterface
{
    public function loadHtml(): void
    {
        $this->setTitle("Useless sidebar");

        $item = new SidebarItem("Logs Data Table", "https://whmcs.docker/index.php?mg-page=Home&m=TTSGGSModule");
        $item2 = new SidebarItem("Service Information", "https://whmcs.docker/index.php?mg-page=Home&mg-action=ServiceInformation&m=TTSGGSModule");
        $item3 = new SidebarItem("Industrial Disk", "https://whmcs.docker/index.php?mg-page=Home&m=TTSGGSModule");
        $item4 = new SidebarItem("Servers Information");

        $item4->addElement(new SidebarSampleButton());

        $this->addItem($item);
        $this->addItem($item2);
        $this->addItem($item3);
        $this->addItem($item4);
    }
}