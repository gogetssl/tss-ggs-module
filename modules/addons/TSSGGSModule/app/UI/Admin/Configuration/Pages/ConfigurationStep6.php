<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Pages;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Content\Step6;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Shared\WizardLayout;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ClientAreaInterface;
use ModulesGarden\TSSGGSModule\Components\Container\Container;


class ConfigurationStep6 extends Container implements AdminAreaInterface, ClientAreaInterface
{
    public function loadHtml(): void
    {
        $this->addElement((new WizardLayout())->build(6, new Step6()));
    }
}
