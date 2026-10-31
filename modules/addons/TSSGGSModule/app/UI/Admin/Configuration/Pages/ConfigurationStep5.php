<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Pages;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Content\Step5;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Shared\WizardLayout;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ClientAreaInterface;
use ModulesGarden\TSSGGSModule\Components\Container\Container;


class ConfigurationStep5 extends Container implements AdminAreaInterface, ClientAreaInterface
{
    public function loadHtml(): void
    {
        $this->addElement((new WizardLayout())->build(5, new Step5()));
    }
}
