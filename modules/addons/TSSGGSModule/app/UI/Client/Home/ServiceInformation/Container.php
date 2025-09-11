<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Client\Home\ServiceInformation;

use ModulesGarden\TSSGGSModule\App\UI\Client\Sidebars\MainSidebar;
use ModulesGarden\TSSGGSModule\Components\LayoutWithSidebar\LayoutWithSidebar;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ClientAreaInterface;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\ServiceInformation\ServiceInformation;

class Container extends LayoutWithSidebar implements ClientAreaInterface
{
    public function loadHtml(): void
    {
        $table = new ServiceInformation();
        $this->addSidebar(new MainSidebar());
        $this->addElement($table);
    }
}