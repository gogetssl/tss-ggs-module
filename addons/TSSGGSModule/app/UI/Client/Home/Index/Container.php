<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Client\Home\Index;

use ModulesGarden\TSSGGSModule\App\UI\Client\Sidebars\MainSidebar;
use ModulesGarden\TSSGGSModule\Components\LayoutWithSidebar\LayoutWithSidebar;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ClientAreaInterface;
use ModulesGarden\TSSGGSModule\Packages\Logs\UI\Pages\LogsDataTable;

class Container extends LayoutWithSidebar implements ClientAreaInterface
{
    public function loadHtml(): void
    {
        $table = new LogsDataTable();
        $this->addSidebar(new MainSidebar());
        $this->addElement($table);
    }
}