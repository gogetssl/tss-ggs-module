<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\BasicElements\PreBlocks;

use ModulesGarden\TSSGGSModule\Components\PreBLock\PreBlock;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;


class PreBlockStatic extends PreBlock implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setContent('This is sample content of pre block');
    }
}
