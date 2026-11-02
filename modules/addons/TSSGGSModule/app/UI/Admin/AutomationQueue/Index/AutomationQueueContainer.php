<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\AutomationQueue\Index;

use ModulesGarden\TSSGGSModule\App\UI\Admin\AutomationQueue\Index\DataTables\Queue\DataTable;
use ModulesGarden\TSSGGSModule\Components\Alert\AlertInfo;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;

class AutomationQueueContainer extends Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        if(!\ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings::enabled('hideInfoMessages'))
        {
            $alert = new AlertInfo();
            $alert->setText($this->translate('queueInfo'));
            $this->addElement($alert);
        }

        $this->addElement(new DataTable());
    }
}
