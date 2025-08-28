<?php

namespace ModulesGarden\TTSGGSModule\Fragments\ConfigurableHintBox\UI\Widgets;

use ModulesGarden\TTSGGSModule\Components\DropdownMenu\DropdownMenu;
use ModulesGarden\TTSGGSModule\Components\HintsBox\HintsBox;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ToolbarTrait;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\Data\Container;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TTSGGSModule\Fragments\ConfigurableHintBox\UI\Buttons\EditConfiguration;
use ModulesGarden\TTSGGSModule\Fragments\ConfigurableHintBox\UI\Buttons\ExpandButton;
use ModulesGarden\TTSGGSModule\Fragments\ConfigurableHintBox\UI\Buttons\HideButton;
use ModulesGarden\TTSGGSModule\Packages\ModuleSettings\Support\Facades\ModuleSettings;

class ConfigurableHintBox extends HintsBox implements AjaxComponentInterface, AdminAreaInterface
{
    use ToolbarTrait;

    protected bool $hideHintBox = false;
    protected bool $expandHintBox = false;

    public function preLoadHtml(): void
    {
        $burger = new DropdownMenu();
        $burger->addItem(new EditConfiguration($this->getId()));

        $this->hideHintBox = ModuleSettings::get('hideHintBox-' . $this->getId(), false);
        $this->expandHintBox = (new Container(Request::get('ajaxData', [])))->get('expand', false);

        if ($this->hideHintBox)
        {
            $button = ($this->hideHintBox && $this->expandHintBox) ?
                new HideButton($this->getId()) :
                new ExpandButton($this->getId());

            $this->addToToolbar($button);
        }

        $this->addToToolbar($burger);
    }

    public function postLoadHtml(): void
    {
        if ($this->hideHintBox && !$this->expandHintBox)
        {
            $this->setSlot('elements.hints', []);
        }
    }
}