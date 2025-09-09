<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Graphs;

use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Graphs\Graphs\GraphArea;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Graphs\Graphs\GraphBar;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Graphs\Graphs\GraphBubble;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Graphs\Graphs\GraphDoughnut;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Graphs\Graphs\GraphLine;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Graphs\Graphs\GraphPie;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Graphs\Graphs\GraphPolarArea;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Graphs\Graphs\GraphRadar;

class Container extends \ModulesGarden\TSSGGSModule\Components\Container\Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new GraphLine());
        $this->addElement(new GraphArea());
        $this->addElement(new GraphBar());
        $this->addElement(new GraphBubble());
        $this->addElement(new GraphPie());
        $this->addElement(new GraphDoughnut());
        $this->addElement(new GraphPolarArea());
        $this->addElement(new GraphRadar());
    }
}
