<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Home\Tabs\Tabs;

use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxOnLoadInterface;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;


class Tab extends \ModulesGarden\TSSGGSModule\Components\Tab\Tab implements \ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface, AjaxOnLoadInterface
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
