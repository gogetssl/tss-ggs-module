<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Client\Sidebars;

use ModulesGarden\TSSGGSModule\App\UI\Client\Sidebars\Buttons\SidebarSampleButton;
use ModulesGarden\TSSGGSModule\Components\Sidebar\Sidebar;
use ModulesGarden\TSSGGSModule\Components\SidebarItem\SidebarItem;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ClientAreaInterface;

class MainSidebar extends Sidebar implements ClientAreaInterface
{
    public function loadHtml(): void
    {
        $this->setTitle("Useless sidebar");

        $item = new SidebarItem("Logs Data Table", "https://whmcs.docker/index.php?mg-page=Home&m=TSSGGSModule");
        $item2 = new SidebarItem("Service Information", "https://whmcs.docker/index.php?mg-page=Home&mg-action=ServiceInformation&m=TSSGGSModule");
        $item3 = new SidebarItem("Industrial Disk", "https://whmcs.docker/index.php?mg-page=Home&m=TSSGGSModule");
        $item4 = new SidebarItem("Servers Information");

        $item4->addElement(new SidebarSampleButton());

        $this->addItem($item);
        $this->addItem($item2);
        $this->addItem($item3);
        $this->addItem($item4);
    }
}