<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms\ApiSettingsForm;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms\SslSettingsForm;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;

class SslSettingsWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));
        $this->setIcon('shield-check-outline');
        $this->addElement((new \ModulesGarden\TSSGGSModule\Components\Text\Text())->setText('<p class="tssggs-page__desc">' . htmlspecialchars($this->translate('subtitle')) . '</p>'));
        $this->addElement(new SslSettingsForm(false, SslSettingsForm::SCOPE_SSL));
    }
}