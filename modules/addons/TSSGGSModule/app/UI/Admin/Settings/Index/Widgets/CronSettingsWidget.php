<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms\ApiSettingsForm;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms\CronConfigurationForm;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms\CronSettingsForm;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms\FinanceSettingsForm;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms\SslSettingsForm;
use ModulesGarden\TSSGGSModule\Components\PreBlock\PreBlock;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Packages\ModuleSettings\Models\ModuleSettings;

class CronSettingsWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));

        $timestamp = ModuleSettings::where('setting', 'cronLastUpdate')->first();
        $dateCron = isset($timestamp->value) ? date('Y-m-d H:i:s', $timestamp->value) : 'not activated';
        $this->addElement((new Text())->setText($this->translate('cronCommandTitle_processing').'<br>')->setCss('cronCommandTitle'));
        $this->addElement((new Text())->setText($this->translate('cronCommand_processing').$dateCron));
        $this->addElement((new PreBlock())->setContent('*/5 * * * * php -q '.dirname(__DIR__, 6) . '/cron/cronScript.php'));

        $expectedCrons = [
            'ProductPricingUpdate',
            'SSLCertificatesProcessing',
            'SSLCertificates',
            'RenewalNotifyCertificate',
            'ReSyncProducts'
        ];

        foreach($expectedCrons as $cronCommand)
        {
            $cron    = '*/5 * * * *';
            $command = 'php -q ' . dirname(__DIR__, 6) . '/cron/cron.php ' . $cronCommand;
            $this->addElement((new Text())->setText($this->translate('cronCommandTitle_' . $cronCommand).'<br>')->setCss('cronCommandTitle'));
            $this->addElement((new Text())->setText($this->translate('cronCommand_' . $cronCommand)));
            $this->addElement((new PreBlock())->setContent($cron . ' ' . $command));
        }

        $this->addElement(new CronConfigurationForm());

    }
}