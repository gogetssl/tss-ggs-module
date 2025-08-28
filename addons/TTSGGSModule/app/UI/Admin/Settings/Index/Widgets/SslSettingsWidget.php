<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Widgets;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Forms\ApiSettingsForm;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Forms\SslSettingsForm;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;

class SslSettingsWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));
        $this->addElement(new SslSettingsForm());
    }
}