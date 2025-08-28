<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\BasicElements;

use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\BasicElements\LabelsBadgesButtons\LabelsBadgesButtons;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\BasicElements\Modals\Modals;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\BasicElements\PreBlocks\PreBlocks;

class Container extends \ModulesGarden\TTSGGSModule\Components\Container\Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new Alerts\Alerts());
        $this->addElement(new LabelsBadgesButtons());
        $this->addElement(new Modals());
        $this->addElement(new PreBlocks());
    }
}