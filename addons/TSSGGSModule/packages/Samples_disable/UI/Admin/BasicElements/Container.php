<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\BasicElements;

use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\BasicElements\LabelsBadgesButtons\LabelsBadgesButtons;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\BasicElements\Modals\Modals;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\BasicElements\PreBlocks\PreBlocks;

class Container extends \ModulesGarden\TSSGGSModule\Components\Container\Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new Alerts\Alerts());
        $this->addElement(new LabelsBadgesButtons());
        $this->addElement(new Modals());
        $this->addElement(new PreBlocks());
    }
}