<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\BasicElements\PreBlocks;

use ModulesGarden\TTSGGSModule\Components\PreBLock\PreBlock;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;


class PreBlockStatic extends PreBlock implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setContent('This is sample content of pre block');
    }
}
