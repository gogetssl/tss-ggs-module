<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms\GeneralSettingsForm;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;

class GeneralSettingsWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));
        $this->setIcon('web');
        $this->addElement(new GeneralSettingsForm());
    }
}
