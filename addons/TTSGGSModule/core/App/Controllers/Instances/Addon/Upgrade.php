<?php

namespace ModulesGarden\TTSGGSModule\Core\App\Controllers\Instances\Addon;

use ModulesGarden\TTSGGSModule\Core\Contracts\Controllers\AddonControllerInterface;
use ModulesGarden\TTSGGSModule\Core\Module\Addon;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\LogActivity;

class Upgrade extends \ModulesGarden\TTSGGSModule\Core\App\Controllers\Instances\AddonController implements AddonControllerInterface
{
    public function execute($params = []): array
    {
        try
        {
            Addon::upgrade($params);

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
