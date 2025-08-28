<?php

namespace ModulesGarden\TTSGGSModule\Fragments\ConfigurableHintBox\UI\Buttons;

use ModulesGarden\TTSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;

class HideButton extends ExpandButton implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setIcon('chevron-up');
        $this->onClick((new ReloadById($this->widgetId))->withParams(["expand" => 0]));
    }
}