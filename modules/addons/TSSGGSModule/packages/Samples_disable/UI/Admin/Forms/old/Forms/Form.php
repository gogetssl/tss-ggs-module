<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Forms;

use ModulesGarden\TSSGGSModule\Components\Container\ContainerQuarterPage;
use ModulesGarden\TSSGGSModule\Components\Container\ContainerRow;
use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\CopyValueByClass;

class Form extends \ModulesGarden\TSSGGSModule\Components\Form\Form
{
    public function loadHtml(): void
    {
        $main = new ContainerRow();
        $this->addElement($main);

        for ($i = 0; $i < 4; $i++)
        {
            $container = new ContainerQuarterPage();
            $main->addElement($container);

            $widget = new Widget();
            $widget->setTitle('Widget ' . +$i);
            $widget->addToToolbar((new Switcher())->onChange(new CopyValueByClass("widget$i")));
            $container->addElement($widget);

            for ($j = 0; $j < 4; $j++)
            {
                $switcher = new Switcher();
                $switcher->setName("name$i$j");
                $switcher->setCss("widget$i");

                $this->builder->addFieldInContainer($widget, $switcher);
            }
        }
    }
}