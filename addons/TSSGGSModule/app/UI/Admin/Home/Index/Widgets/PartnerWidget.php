<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Libs\SSLCenterApiException;
use ModulesGarden\TSSGGSModule\App\Libs\SSLTrustCenterApi;
use ModulesGarden\TSSGGSModule\App\Libs\SSLCenterApiExtended;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Components\ManagerCard;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Helper;

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