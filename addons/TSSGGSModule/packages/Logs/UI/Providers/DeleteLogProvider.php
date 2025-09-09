<?php

namespace ModulesGarden\TSSGGSModule\Packages\Logs\UI\Providers;

use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Config;
use ModulesGarden\TSSGGSModule\Packages\Logs\Models\Logs;

class DeleteLogProvider extends CrudProvider
{
    public function delete()
    {
        if (!Config::get('logs.delete_logs.enabled', true))
        {
            throw new \Exception('deletingLogsIsNotAllowed');
        }

        $ids = explode(',', $this->formData['id']);

        Logs::whereIn('id', $ids)
            ->delete();
    }
}
