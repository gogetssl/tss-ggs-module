<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ProgressBars;

use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ProgressBars\Widgets\ProgressBarsWidgetBaseStyles;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ProgressBars\Widgets\ProgressBarsWidgetSmall;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ProgressBars\Widgets\ProgressBarsWidgetMedium;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ProgressBars\Widgets\ProgressBarsWidgetLarge;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ProgressBars\Widgets\ProgressBarsWidgetStyles;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ProgressBars\Widgets\ProgressBarsNoLabelWithMarks;

class Container extends \ModulesGarden\TTSGGSModule\Components\Container\Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->addElement(new ProgressBarsWidgetSmall());
        $this->addElement(new ProgressBarsWidgetMedium());
        $this->addElement(new ProgressBarsWidgetLarge());
        $this->addElement(new ProgressBarsWidgetBaseStyles());
        $this->addElement(new ProgressBarsNoLabelWithMarks());
        $this->addElement(new ProgressBarsWidgetStyles());
    }
}