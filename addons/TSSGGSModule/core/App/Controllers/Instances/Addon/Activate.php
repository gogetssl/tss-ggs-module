<?php

namespace ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\Addon;

use ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\AddonControllerInterface;
use ModulesGarden\TSSGGSModule\Core\Module\Addon;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\LogActivity;

class Activate extends \ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\AddonController implements AddonControllerInterface
{
    public function execute($params = []): array
    {
        try
        {
            Addon::activate($params);

            return ['status' => 'success'];
        }
        catch (\Throwable $exc)
        {
            LogActivity::error($exc->getMessage());

            return [
                'status'      => 'error',
                'description' => $exc->getMessage(),
            ];
        }
    }
}
