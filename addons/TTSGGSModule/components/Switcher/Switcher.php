<?php

namespace ModulesGarden\TTSGGSModule\Components\Switcher;

use ModulesGarden\TTSGGSModule\Core\Components\FormFields\FormField;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\AjaxDataProviderTrait;

class Switcher extends FormField
{
    use ComponentsContainerTrait;
    use AjaxDataProviderTrait;

    //use only with setOnOffMode
    const STATE_ON = "on";
    const STATE_OFF = "off";

    public const COMPONENT = 'Switcher';

    public function setOnOffMode(): self
    {
        $this->setSlot('onOffMode', true);

        return $this;
    }
}
