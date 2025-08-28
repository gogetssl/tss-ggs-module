<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ProgressBars\Widgets;

use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Components\ProgressBar\ProgressBar;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Components\Enums\Size;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class ProgressBarsNoLabelWithMarks extends Widget implements AjaxComponentInterface, AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setTitle("No Labels, with marks");

        $bar10 = (new ProgressBar())
            ->setFill(10)
            ->setSize(Size::LARGE)
            ->disableLabel()
            ->enableFillMark();
        $bar30 = (new ProgressBar())
            ->setFill(30)->setSize(Size::LARGE)
            ->disableLabel()
            ->enableFillMark();
        $bar60 = (new ProgressBar())
            ->setFill(60)->setSize(Size::LARGE)
            ->disableLabel()
            ->enableFillMark();
        $bar80 = (new ProgressBar())
            ->setFill(80)->setSize(Size::LARGE)
            ->disableLabel()
            ->enableFillMark();

        $this->addElement($bar30);
        $this->addElement($bar80);
        $this->addElement($bar60);
        $this->addElement((new Container())->addElement($bar10));
    }
}