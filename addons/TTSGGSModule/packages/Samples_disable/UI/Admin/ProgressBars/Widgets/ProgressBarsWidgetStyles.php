<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ProgressBars\Widgets;

use ModulesGarden\TTSGGSModule\Components\ProgressBar\ProgressBar;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Components\Enums\BackgroundColor;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class ProgressBarsWidgetStyles extends Widget implements AjaxComponentInterface, AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setTitle("All Styles Examples Progress Bars");

        $oClass = new \ReflectionClass(new BackgroundColor());

        foreach ($oClass->getConstants() as $type => $class) {
            $bar = (new ProgressBar())->setText($type)->setFill(rand(10,95))->setType($class);
            $this->addElement($bar);
        }
    }
}