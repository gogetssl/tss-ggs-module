<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Pages;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Content\Step7;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Shared\WizardLayout;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Components\Container\Container;

class ConfigurationStep7 extends Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->addElement((new WizardLayout())->build(7, new Step7()));
    }
}
