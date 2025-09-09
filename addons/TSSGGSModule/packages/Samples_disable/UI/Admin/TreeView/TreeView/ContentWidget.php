<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TreeView\TreeView;

use ModulesGarden\TSSGGSModule\Components\Alert\AlertDanger;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TreeView\DataTable\QueryDataTable;

class ContentWidget extends Widget implements AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->setTitle('Some Content');
        $this->setId('content-widget');
    }

    public function loadData(): void
    {
        $ajaxContent = \ModulesGarden\TSSGGSModule\Core\Support\Facades\Request::get('ajaxData')['content'];

        $this->addElement((new AlertDanger())->setText($ajaxContent.' '.time()));
        $this->addElement((new QueryDataTable())->setAjaxData([
            'ajaxContent' => $ajaxContent
        ]));
    }
}