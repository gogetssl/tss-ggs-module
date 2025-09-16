<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Components\AnnouncementCard;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;


class AnnouncementsWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));
        $this->setIcon('radio');
        $this->setSlot('customcontentclass', 'announcements');

        $configuredApis = Helpers::getConfiguredApis();

        foreach($configuredApis as $vendor => $api)
        {
            $announcements = $api->getAnnouncements();

            foreach($announcements as $announcement)
            {
                $owner = strtoupper($announcement['owner']);
                if(array_key_exists($owner, $configuredApis))
                {
                    $vendorDisplay = '';
                    if(count($configuredApis) > 1)
                    {
                        $vendorDisplay = Helpers::vendorToDisplay($vendor);
                    }

                    $this->addElement(new AnnouncementCard(
                                          $announcement['title'],
                                          $vendorDisplay,
                                          $announcement['body'],
                                          $announcement['date'],
                                          $announcement['category']
                                      )
                    );
                }
            }
            break; //if all apis contain all announcements, brake after first api call
        }
    }
}