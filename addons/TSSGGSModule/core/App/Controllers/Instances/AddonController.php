<?php

namespace ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances;

use ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\Http\AddonIntegration;
use ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\Http\AdminServicesTabFieldsIntegration;
use ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\Http\ConfigOptionsIntegration;
use ModulesGarden\TSSGGSModule\Core\App\Controllers\ResponseResolver;
use ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\DefaultControllerInterface;
use ModulesGarden\TSSGGSModule\Core\Traits\AppParams;
use ModulesGarden\TSSGGSModule\Core\UI\ViewAjax;
use ModulesGarden\TSSGGSModule\Core\UI\ViewConfigOptions;
use ModulesGarden\TSSGGSModule\Core\UI\ViewIntegrationAddon;
use function ModulesGarden\TSSGGSModule\Core\make;

abstract class AddonController implements DefaultControllerInterface
{
    public function runExecuteProcess($params = null)
    {
        $result = $this->execute($params);

        if ($this->isValidIntegrationCallback($result))
        {
            $method = $result[1];

            $result = make($result[0])->$method();

//            echo '<pre>';
//            print_r($params);
//            print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
//            exit;
        }

        if ($result instanceof ViewAjax)
        {
            $this->resolveAjax($result);
        }

        if (!$result instanceof ViewIntegrationAddon && !$result instanceof ViewConfigOptions)
        {
            return $result;
        }

        $addonIntegration = $this->getIntegrationController($params['action']);

        return $addonIntegration->runExecuteProcess($result);
    }

    public function isValidIntegrationCallback($callback = null)
    {
        return is_array($callback) && isset($callback[0]) && isset($callback[1]) && method_exists($callback[0], $callback[1]);
    }

    public function resolveAjax($resault)
    {
        $ajaxResponse = $resault->getResponse();

        $resolver = new ResponseResolver($ajaxResponse);

        $resolver->resolve();
    }

    protected function getIntegrationController($action = null)
    {
        switch ($action)
        {
            case 'ConfigOptions':
                return make(ConfigOptionsIntegration::class);
            case 'AdminServicesTabFields':
                return make(AdminServicesTabFieldsIntegration::class);
            default:
                return make(AddonIntegration::class);
        }
    }
}
