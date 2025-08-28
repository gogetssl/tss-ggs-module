<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Client\Home\Index;

use ModulesGarden\TTSGGSModule\App\UI\Client\Sidebars\MainSidebar;
use ModulesGarden\TTSGGSModule\Components\LayoutWithSidebar\LayoutWithSidebar;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ClientAreaInterface;
use ModulesGarden\TTSGGSModule\Packages\Logs\UI\Pages\LogsDataTable;

class Container extends LayoutWithSidebar implements ClientAreaInterface
{
    public function loadHtml(): void
    {
        $table = new LogsDataTable();
        $this->addSidebar(new MainSidebar());
        $this->addElement($table);
    }
}