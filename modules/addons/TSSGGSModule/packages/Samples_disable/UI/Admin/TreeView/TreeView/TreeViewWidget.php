<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TreeView\TreeView;

use ModulesGarden\TSSGGSModule\Components\TreeListContainer\TreeListContainer;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;

class TreeViewWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle('Tree View');


        $treeViewContainer = new TreeListContainer();

        for ($i = 0; $i < 10; $i++)
        {
            $item = new \ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TreeView\TreeView\TreeListItem();
            $item->setTitle($i);

            $treeViewContainer->addElement($item);
        }


        $this->addElement($treeViewContainer);
    }
}