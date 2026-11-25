<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Client\Home\Index;

use ModulesGarden\TSSGGSModule\App\UI\Client\Sidebars\MainSidebar;
use ModulesGarden\TSSGGSModule\Components\LayoutWithSidebar\LayoutWithSidebar;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ClientAreaInterface;

class Container extends LayoutWithSidebar implements ClientAreaInterface
{
    public function loadHtml(): void
    {
        $this->addSidebar(new MainSidebar());
    }
}
