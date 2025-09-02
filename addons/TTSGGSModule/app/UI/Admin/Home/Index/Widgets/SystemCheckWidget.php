<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\Widgets;

use Dom\Text;
use ModulesGarden\TTSGGSModule\App\Models\CronCheck;
use ModulesGarden\TTSGGSModule\Components\Alert\Alert;
use ModulesGarden\TTSGGSModule\Components\Alert\AlertDanger;
use ModulesGarden\TTSGGSModule\Components\Hint\Hint;
use ModulesGarden\TTSGGSModule\Components\HintsBox\HintsBox;
use ModulesGarden\TTSGGSModule\Components\ListInfo\ListInfo;
use ModulesGarden\TTSGGSModule\Components\ListInfo\ListInfoItem;
use ModulesGarden\TTSGGSModule\Components\ListSimple\ListSimple;
use ModulesGarden\TTSGGSModule\Components\Text\TextBold;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Packages\ModuleSettings\Models\ModuleSettings;

class SystemCheckWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));
        $this->setIcon('hospital-box-outline');

        $date = date('Y-m-d H:i:s');

        $expectedCrons = [
            'ProductPricingUpdate',
            'SSLCertificates',
            'RenewalNotifyCertificate',
            'ReSyncProducts',
            'SSLCertificatesProcessing',
        ];

        $list = new ListSimple();
        $list->addClass('system-check-list');

        $cronStatus = 'success';

        $timestamp = ModuleSettings::where('setting', 'cronLastUpdate')->first();
        if(isset($timestamp->value))
        {
            $list->addItem('cronScript'.' - <span class="green">' . date('Y-m-d H:i:s', $timestamp->value) . '</span>');
        }
        else
        {
            $list->addItem('cronScript'.' - <span class="red">' . $this->translate('never') . '</span>');
            $cronStatus = 'error';
        }

        foreach($expectedCrons as $expectedCron)
        {
            $cronCheck = CronCheck::where('type', $expectedCron)->first();

            if($cronCheck)
            {
                $date  = $cronCheck->last_run;
                $error = trim($cronCheck->last_error);
                $item  = $expectedCron . ' - <span class="green">' . $date . '</span>';

                if($error)
                {
                    $item .= ' - <span class="red">' . $this->translate('error') . ': ' . $error . '</span>';
                }

                $list->addItem($item);
            }
            else
            {
                $list->addItem($expectedCron . ' - <span class="red">' . $this->translate('never') . '</span>');
                $cronStatus = 'error';
            }
        }

        if($cronStatus == 'error')
        {
            $alert = new AlertDanger();
            $alert->setText($this->translate('system_check_alert'));
            $this->addElement($alert);
        }

        $this->addElement((new TextBold())->setText($this->translate('system_check_crons')));
        $this->addElement($list);
/*
        $this->addElement((new TextBold())->setText($this->translate('system_check_updates')));
        $list = new ListSimple();
        $list->addClass('system-check-list');
        $list->addItem('placeholder');
        $this->addElement($list);
*/

    }
}