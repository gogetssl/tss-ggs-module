<?php

namespace ModulesGarden\TTSGGSModule\Core\Routing\RoutesProviders;

use ModulesGarden\TTSGGSModule\Core\Contracts\Routing\RouteProviderInterface;
use ModulesGarden\TTSGGSModule\Core\DependencyInjection\PackageServices;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\Routing\Route;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Config;
use function ModulesGarden\TTSGGSModule\Core\make;

class Standard implements RouteProviderInterface
{
    public function find(\Symfony\Component\HttpFoundation\Request $request, string $level): ?Route
    {
        $page   = $this->getPage($request, $level);
        $action = $this->getAction($request, $level);


        if ($callable = $this->tryDefaultControllers($level, $page, $action))
        {
            return new Route($callable);
        }

        if ($callable = $this->tryPackageControllers($level, $page, $action))
        {
            return new Route($callable);
        }

        return null;
    }

    /**
     * Default controllers from app directory
     * @param string $level
     * @param string $page
     * @param string $action
     * @return string|null
     */
    protected function tryDefaultControllers(string $level, string $page, string $action): ?string
    {
        $namespace = '\ModulesGarden\TTSGGSModule\App\Http\\' . ucfirst($level) . '\\' . ucfirst($page);
        if (class_exists($namespace) && method_exists($namespace, $action))
        {
            return $namespace . '@' . $action;
        }

        return null;
    }

    /**
     * Controllers from enabled packages
     * @param string $level
     * @param string $page
     * @param string $action
     * @return string|null
     */
    protected function tryPackageControllers(string $level, string $page, string $action): ?string
    {
        $controllers = make(PackageServices::class)->getHttpControllers();

        foreach ($controllers[strtolower($level)] as $controller)
        {
            if ($this->endsWith($controller, $page) && method_exists($controller, $action))
            {
                return $controller . '@' . $action;
            }
        }

        return null;
    }

    protected function endsWith(string $haystack, string $needle): bool
    {
        return substr_compare($haystack, $needle, -strlen($needle), null, true) === 0;
    }

    protected function getPage(\Symfony\Component\HttpFoundation\Request $request, string $level)
    {
        return $request->get(ModuleConstants::CONTROLLER_PAGE_PARAMETER, Config::get('configuration.' . ModuleConstants::getLevel() . 'AreaController'));
    }

    protected function getAction(\Symfony\Component\HttpFoundation\Request $request, string $level)
    {
        return $request->get(ModuleConstants::CONTROLLER_ACTION_PARAMETER, ModuleConstants::DEFAULT_CONTROLLER_ACTION);
    }
}


