<?php

namespace ModulesGarden\TTSGGSModule\Fragments\ConfigurableHintBox\UI\Buttons;

use ModulesGarden\TTSGGSModule\Components\DropdownMenuItem\DropdownMenuItem;
use ModulesGarden\TTSGGSModule\Core\Components\Action;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Fragments\ConfigurableHintBox\UI\Modals\EditConfigurationModal;

class EditConfiguration extends DropdownMenuItem implements AdminAreaInterface
{
    protected string $widgetId;

    public function __construct(string $widgetId)
    {
        parent::__construct();

        $this->widgetId = $widgetId;
    }

    public function loadHtml(): void
    {
        $this->setIcon('cog');
        $this->onClick(Action::modalLoad(new EditConfigurationModal())->withParams(["widgetId" => $this->widgetId ]));
    }
}