<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\AutomationQueue\Index\DataTables\Queue\Providers;

use ModulesGarden\TSSGGSModule\App\Libs\AutoInstallQueue;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;

class ResetTaskProvider extends CrudProvider
{
    public function update()
    {
        AutoInstallQueue::resetTask((int)$this->formData['id']);
    }
}
