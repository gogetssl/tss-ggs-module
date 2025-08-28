<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\Components\AssistantCard;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;

class SupportWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));
        $this->setIcon('headset');
        $this->setSlot('customcontentclass', 'support-assistance');
        $configuredApis = Helpers::getConfiguredApis();

        if(isset($configuredApis["TSS"]) && isset($configuredApis["GGS"]))
        {
            $details = [
                [
                    'title'   => $this->translate('phone'),
                    'display' => '727-388-4240',
                ],
                [
                    'title'   => $this->translate('ttsEmail'),
                    'display' => 'support@thesslstore.com',
                    'url'     => 'mailto:support@thesslstore.com'
                ],
                [
                    'title'   => $this->translate('ggsEmail'),
                    'display' => 'support@gogetssl.com',
                    'url'     => 'mailto:support@gogetssl.com'
                ],
                [
                    'title'   => $this->translate('helpdesk'),
                    'display' => $this->translate('helpdeskDisplay'),
                    'url'     => 'testUrl3',
                ],
                [
                    'title'   => $this->translate('features'),
                    'display' => $this->translate('featuresDisplay'),
                    'url'     => 'testUrl3',
                ],
            ];
        }
        elseif(isset($configuredApis["TSS"]))
        {
            $details = [
                [
                    'title'   => $this->translate('phone'),
                    'display' => '727-388-4240',
                ],
                [
                    'title'   => $this->translate('email'),
                    'display' => 'support@thesslstore.com',
                    'url'     => 'mailto:support@thesslstore.com'
                ],
                [
                    'title'   => 'SSLHelpdesk.com:',
                    'display' => 'White Label Support Articles',
                    'url'     => 'testUrl3',
                ],
                [
                    'title'   => 'SSLFeatures.com:',
                    'display' => 'Informative White-Label Product Pages',
                    'url'     => 'testUrl3',
                ],
            ];
        }
        elseif(isset($configuredApis["GGS"]))
        {
            $details = [
                [
                    'title'   => $this->translate('email'),
                    'display' => 'support@gogetssl.com',
                    'url'     => 'mailto:support@gogetssl.com'
                ],
                [
                    'title'   => 'SSLFeatures.com:',
                    'display' => 'Informative White-Label Product Pages',
                    'url'     => 'testUrl3',
                ],
                [
                    'title'   => '',
                    'display' => 'www.gogetssl.com',
                    'url'     => 'www.gogetssl.com',
                ],
            ];
        }

        $this->addElement(new AssistantCard($details));
    }
}