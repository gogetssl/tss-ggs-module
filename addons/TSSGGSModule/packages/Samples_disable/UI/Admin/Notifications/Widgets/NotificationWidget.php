<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Notifications\Widgets;

use ModulesGarden\TSSGGSModule\Components\Container\ContainerRow;
use ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Notifications\Components\Notifications;

class NotificationWidget extends Widget implements AjaxComponentInterface, AdminAreaInterface
{
    public function loadHtml(): void
    {
        $notificationDropdown = new Notifications();

        $bar = new Toolbar();
        $bar->addElement(new ContainerRow());
        $bar->addElement($notificationDropdown);

        $this->addElement($bar);
    }
}