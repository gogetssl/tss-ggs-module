<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms\LogsSettingsForm;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;

/**
 * Settings > Logs: switch each log the module offers on or off
 */
class LogsSettingsWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));
        $this->setIcon('text-box-outline');
        $this->addElement((new \ModulesGarden\TSSGGSModule\Components\Text\Text())->setText('<p class="tssggs-page__desc">' . htmlspecialchars($this->translate('subtitle')) . '</p>'));
        $this->addElement(new LogsSettingsForm());
    }
}
