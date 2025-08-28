<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Home\Tabs\Tabs;

use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxOnLoadInterface;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;


class Tab extends \ModulesGarden\TTSGGSModule\Components\Tab\Tab implements \ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface, AjaxOnLoadInterface
{
    public function loadData(): void
    {
        $this->setContent('load data! <pre>' . print_r((Request::getFacadeRoot())->getAll(), true));
    }

    public function loadHtml(): void
    {
        $this->setTitle(rand(0, 100));
        $this->setContent('load html!');
    }
}
