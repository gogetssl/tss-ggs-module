<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Widgets;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Forms\ApiSettingsForm;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;

class ApiSettingsWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));
        $this->addElement(new ApiSettingsForm());
    }
}