<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\App\Libs\SSLCenterApiException;
use ModulesGarden\TTSGGSModule\App\Libs\SSLTrustCenterApi;
use ModulesGarden\TTSGGSModule\App\Libs\SSLCenterApiExtended;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\Components\ManagerCard;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Helper;

class PartnerWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));
        $this->setIcon('account');
        $configuredApis  = Helpers::getConfiguredApis();
        $processedEmails = [];
        $countryCode     = \WHMCS\Config\Setting::getValue("DefaultCountry");

        foreach($configuredApis as $vendor => $api)
        {
            $managerData = $api->getManager($countryCode);

            if(!in_array($managerData['email'], $processedEmails))
            {
                $this->addElement(new ManagerCard($managerData['name'], $managerData['email'], $managerData['phone'], $managerData['photo']));
                $processedEmails[] = $managerData['email'];
            }
        }
    }
}