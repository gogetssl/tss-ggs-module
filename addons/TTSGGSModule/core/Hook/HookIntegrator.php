<?php

namespace ModulesGarden\TTSGGSModule\Core\Hook;

use ModulesGarden\TTSGGSModule\Core\DependencyInjection;
use ModulesGarden\TTSGGSModule\Core\Hook\Integration\IntegrationChecker;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Config;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Params;
use ModulesGarden\TTSGGSModule\Core\UI\View;
use ModulesGarden\TTSGGSModule\Core\UI\ViewAjax;
use function ModulesGarden\TTSGGSModule\Core\Helper\isAdmin;
use function ModulesGarden\TTSGGSModule\Core\make;

/**
 *  class HookIntegrator
 *  Prepares a views basing on /App/Integrations/Admin/ & /App/Integrations/Client controlers
 *  to be injected on WHMCS subpages
 */
class HookIntegrator
{

    /** @var null|string
     * HTML data to be returned as a result of the integration process
     */
    protected array $integrationData = [];

    /** @var array
     *  avalible hook integrations list
     */
    protected array $integrations = [];
    protected array $hookParams = [];

    /**
     * @var bool
     *  determines if  works on admin or client area side
     */
    protected $isAdmin = false;

    public function __construct($hookParams)
    {
        $this->setHookParams($hookParams);

        $this->checkIsAdmin();

        $this->integrate();
    }

    public function setHookParams($hookParams)
    {
        if (is_array($hookParams))
        {
            Params::createFrom($hookParams);
            $this->hookParams = $hookParams;
        }

        return $this;
    }

    /**
     * determines if  works on admin or client area side
     */
    public function checkIsAdmin()
    {
        $this->isAdmin = isAdmin();
    }

    /**
     * starts whole integration process
     */
    protected function integrate()
    {
        $this->loadAvailablePackagesIntegrations();
        $this->loadAvailableModuleIntegrations();
        $this->loadIntegrationData();
    }

    /**
     * search integration in packages dirs /packages/'package'/Integrations
     */
    protected function loadAvailablePackagesIntegrations()
    {
        $packages = Config::get('packages', []);

        foreach (array_keys(array_filter($packages, fn($enabled) => (bool)$enabled)) as $package)
        {
            $this->loadAvailableIntegrations('packages', $package, 'Integrations');
        }
    }

    /**
     * search integration in Module main dir /app/Integrations/Admin(Client)
     */
    protected function loadAvailableModuleIntegrations()
    {
        $this->loadAvailableIntegrations('app', 'Integrations', ucfirst(ModuleConstants::getLevel()));
    }

    /**
     * loads available integration from selected dirs
     */
    protected function loadAvailableIntegrations(string ...$dirs)
    {
        $hooksPath = ModuleConstants::getFullPath(...$dirs);
        $hooksNamespace = ModuleConstants::getFullNamespace(...array_map(fn($element) => ucfirst($element), $dirs));

        if (!file_exists($hooksPath) || !is_readable($hooksPath))
        {
            return false;
        }

        $files = scandir($hooksPath, 1);
        if ($files)
        {
            foreach ($files as $key => $value)
            {
                if ($value === '.' || $value === '..' || !(stripos($value, '.php') > 0))
                {
                    unset($files[$key]);
                    continue;
                }

                $this->addIntegration($hooksNamespace . '\\' . str_replace('.php', '', $value));
            }
        }
    }

    /**
     * adds integration instance to the integrations list for current page
     * @param null|string $className
     * @return bool
     */
    protected function addIntegration(?string $integrationClass)
    {
        if (!class_exists($integrationClass) || !is_subclass_of($integrationClass, AbstractHookIntegrationController::class))
        {
            return false;
        }

        //creates an instance of integration class
        $integrationInstance = DependencyInjection::create($integrationClass);
        if (method_exists($integrationInstance, 'validate') && !$integrationInstance->validate($this->hookParams))
        {
            return false;
        }

        //check if integration should be added to current page
        if (!$this->validateIntegrationInstance($integrationInstance))
        {
            return false;
        }

        $this->integrations[] = $integrationInstance;
    }

    /**
     * check if the integration should be added to current page
     * @param null|AbstractHookIntegrationController $instance
     * @return bool
     */
    public function validateIntegrationInstance($instance = null)
    {
        if (!is_subclass_of($instance, AbstractHookIntegrationController::class))
        {
            return false;
        }

        $integration = $instance->getIntegration();

        if ($integration->getPlacementSelector() === null)
        {
            return false;
        }

        $callback = $integration->getControllerCallback();

        if (!method_exists($callback->getController(), $callback->getAction()))
        {
            return false;
        }

        return true;
    }

    public function loadIntegrationData()
    {
        foreach ($this->integrations as $integrationController)
        {
            if (!is_subclass_of($integrationController, AbstractHookIntegrationController::class))
            {
                continue;
            }

            $integration = $integrationController->getIntegration();

            if (!IntegrationChecker::isApplicable($integration, $this->hookParams))
            {
                continue;
            }

            $callbackData = $integration->getControllerCallback();

            /** @var
             * $integrationResult \ModulesGarden\TTSGGSModule\Core\UI\View
             */
            $integrationResult = call_user_func([make($callbackData->getController()), $callbackData->getAction()]);
            if (!($integrationResult instanceof View) && !($integrationResult instanceof ViewAjax))
            {
                continue;
            }

            $view = new HookIntegratorView($integrationResult);

            $this->updateIntegrationData($integration, $view->getHTML());
        }
    }

    protected function updateIntegrationData($integrationDetails, $htmlData)
    {
        if (!is_string($htmlData) || $htmlData === '' || !$integrationDetails || !is_object($integrationDetails))
        {
            return false;
        }

        $this->integrationData[] = [
            'htmlData'           => $htmlData,
            'integrationDetails' => $integrationDetails,
        ];
    }

    /**
     * returns integration output
     */
    public function getHtmlCode()
    {
        return $this->getWrapperHtml();
    }

    protected function getWrapperHtml()
    {
        if (!$this->integrationData)
        {
            return null;
        }

        $wrapper = new HookIntegrationsWrapper($this->integrationData);

        return $wrapper->getHtml();
    }
}
