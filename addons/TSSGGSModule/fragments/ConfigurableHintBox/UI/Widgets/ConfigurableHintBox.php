<?php

namespace ModulesGarden\TSSGGSModule\Fragments\ConfigurableHintBox\UI\Widgets;

use ModulesGarden\TSSGGSModule\Components\DropdownMenu\DropdownMenu;
use ModulesGarden\TSSGGSModule\Components\HintsBox\HintsBox;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ToolbarTrait;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\Data\Container;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TSSGGSModule\Fragments\ConfigurableHintBox\UI\Buttons\EditConfiguration;
use ModulesGarden\TSSGGSModule\Fragments\ConfigurableHintBox\UI\Buttons\ExpandButton;
use ModulesGarden\TSSGGSModule\Fragments\ConfigurableHintBox\UI\Buttons\HideButton;
use ModulesGarden\TSSGGSModule\Packages\ModuleSettings\Support\Facades\ModuleSettings;

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