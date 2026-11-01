<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms\CronConfigurationForm;
use ModulesGarden\TSSGGSModule\Components\PreBlock\PreBlock;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;

/**
 * CRON settings: ONE cron line for the whole module (RunAll starts every job when it is due), then the form with one
 * block per job (description, last run and how often it runs).
 */
class CronSettingsWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));

        $script = dirname(__DIR__, 6) . '/cron/cron.php';

        $this->addElement((new Text())->setText($this->translate('oneLineTitle') . '<br>')->setCss('cronCommandTitle'));
        $this->addElement((new Text())->setText($this->translate('oneLineInfo')));
        $this->addElement((new PreBlock())->setContent('*/5 * * * * php -q ' . $script . ' RunAll'));

        $this->addElement(new CronConfigurationForm());
    }
}
