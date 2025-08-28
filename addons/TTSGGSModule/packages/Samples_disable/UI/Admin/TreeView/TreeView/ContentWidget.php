<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView\TreeView;

use ModulesGarden\TTSGGSModule\Components\Alert\AlertDanger;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView\DataTable\QueryDataTable;

class ContentWidget extends Widget implements AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->setTitle('Some Content');
        $this->setId('content-widget');
    }

    public function loadData(): void
    {
        $ajaxContent = \ModulesGarden\TTSGGSModule\Core\Support\Facades\Request::get('ajaxData')['content'];

        $this->addElement((new AlertDanger())->setText($ajaxContent.' '.time()));
        $this->addElement((new QueryDataTable())->setAjaxData([
            'ajaxContent' => $ajaxContent
        ]));
    }
}