<?php

namespace ModulesGarden\TSSGGSModule\Components\Switcher;

use ModulesGarden\TSSGGSModule\Core\Components\FormFields\FormField;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\AjaxDataProviderTrait;

class Switcher extends FormField
{
    use ComponentsContainerTrait;
    use AjaxDataProviderTrait;

    //use only with setOnOffMode
    const STATE_ON = "on";
    const STATE_OFF = "off";

    public const COMPONENT = 'Switcher';

    public function __construct()
    {
        parent::__construct();

        // the switch is shown as a Yes / No choice
        $this->setSlot('yesLabel', (string)$this->translate('yes', [], ['admin.switcher']));
        $this->setSlot('noLabel', (string)$this->translate('no', [], ['admin.switcher']));
    }

    public function setOnOffMode(): self
    {
        $this->setSlot('onOffMode', true);

        return $this;
    }
}
