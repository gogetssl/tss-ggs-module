<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Forms;

use ModulesGarden\TTSGGSModule\Components\Container\ContainerQuarterPage;
use ModulesGarden\TTSGGSModule\Components\Container\ContainerRow;
use ModulesGarden\TTSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\CopyValueByClass;

class Form extends \ModulesGarden\TTSGGSModule\Components\Form\Form
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