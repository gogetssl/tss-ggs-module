<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Widgets;

use ModulesGarden\TSSGGSModule\Components\SidebarItem\SidebarItem;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;

class Sidebar extends \ModulesGarden\TSSGGSModule\Components\Sidebar\Sidebar
{
    /** page => Material Design icon */
    const ICONS = [
        'apiSettings'       => 'key-outline',
        'generalSettings'   => 'web',
        'sslSettings'       => 'shield-check-outline',
        'automatedSettings' => 'sync',
        'financeSettings'   => 'email-outline',
        'cronSettings'      => 'file-clock-outline',
        'logsSettings'      => 'text-box-outline',
    ];

    function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));

        $currentPage = Request::get('settings-page')?:'apiSettings';
        $pages       = [
            'apiSettings',
            'generalSettings',
            'sslSettings',
            'automatedSettings',
            'financeSettings',
            'cronSettings',
            'logsSettings',
        ];

        foreach($pages as $page)
        {
            $item = (new SidebarItem($this->translate($page)))->setIcon(self::ICONS[$page])->setUrl('addonmodules.php?module=TSSGGSModule&mg-page=settings&settings-page=' . $page);

            if($page == $currentPage)
            {
                $item->setActive(true);
            }
            else
            {
                $item->setActive(false);
            }

            $this->addItem($item);
        }
    }
}
