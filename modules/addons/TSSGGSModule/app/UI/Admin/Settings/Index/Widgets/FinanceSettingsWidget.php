<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms\ApiSettingsForm;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms\FinanceSettingsForm;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms\SslSettingsForm;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;

class FinanceSettingsWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));
        $this->addElement(new FinanceSettingsForm());
    }
}