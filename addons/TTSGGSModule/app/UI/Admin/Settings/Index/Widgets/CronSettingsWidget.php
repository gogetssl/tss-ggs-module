<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Widgets;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Forms\ApiSettingsForm;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Forms\CronConfigurationForm;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Forms\CronSettingsForm;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Forms\FinanceSettingsForm;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Forms\SslSettingsForm;
use ModulesGarden\TTSGGSModule\Components\PreBlock\PreBlock;
use ModulesGarden\TTSGGSModule\Components\Text\Text;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Packages\ModuleSettings\Models\ModuleSettings;

class CronSettingsWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));

        $timestamp = ModuleSettings::where('setting', 'cronLastUpdate')->first();
        $dateCron = isset($timestamp->value) ? date('Y-m-d H:i:s', $timestamp->value) : 'not activated';
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
            $this->addElement((new Text())->setText($this->translate('cronCommand_' . $cronCommand)));
            $this->addElement((new PreBlock())->setContent($cron . ' ' . $command));
        }

        $this->addElement(new CronConfigurationForm());

    }
}