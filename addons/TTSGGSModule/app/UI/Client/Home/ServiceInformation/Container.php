<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Client\Home\ServiceInformation;

use ModulesGarden\TTSGGSModule\App\UI\Client\Sidebars\MainSidebar;
use ModulesGarden\TTSGGSModule\Components\LayoutWithSidebar\LayoutWithSidebar;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ClientAreaInterface;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\ServiceInformation\ServiceInformation;

class Container extends LayoutWithSidebar implements ClientAreaInterface
{
    public function loadHtml(): void
    {
        $table = new ServiceInformation();
        $this->addSidebar(new MainSidebar());
        $this->addElement($table);
    }
}