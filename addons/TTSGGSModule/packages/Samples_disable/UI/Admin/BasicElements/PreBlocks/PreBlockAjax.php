<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\BasicElements\PreBlocks;

use ModulesGarden\TTSGGSModule\Components\PreBlock\PreBlock;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxOnLoadInterface;


class PreBlockAjax extends PreBlock implements AdminAreaInterface, AjaxOnLoadInterface
{
    public function loadData(): void
    {
        $this->setContent('This is content from ajax!');
    }

    public function loadHtml(): void
    {
        $this->setContent('This is sample content of pre    block');
    }
}
