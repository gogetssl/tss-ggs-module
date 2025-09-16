<?php

namespace ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances;

use ModulesGarden\TSSGGSModule\Core\App\Controllers\Exceptions\PageNotFound;
use ModulesGarden\TSSGGSModule\Core\App\Controllers\Instances\Http\ErrorPage;
use ModulesGarden\TSSGGSModule\Core\App\Controllers\ResponseResolver;
use ModulesGarden\TSSGGSModule\Core\App\Controllers\Router;
use ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\ClientAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\DefaultControllerInterface;
use ModulesGarden\TSSGGSModule\Core\DependencyInjection;
use ModulesGarden\TSSGGSModule\Core\Exceptions\UserException;
use ModulesGarden\TSSGGSModule\Core\ModuleConstants;
use ModulesGarden\TSSGGSModule\Core\Routing\Middleware\Processors\Controller;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TSSGGSModule\Core\Traits\AppParams;
use ModulesGarden\TSSGGSModule\Core\Traits\ErrorCodesLibrary;
use ModulesGarden\TSSGGSModule\Core\Traits\IsAdmin;
use ModulesGarden\TSSGGSModule\Core\Traits\Lang;
use ModulesGarden\TSSGGSModule\Core\Traits\OutputBuffer;
use ModulesGarden\TSSGGSModule\Core\Traits\Params;

abstract class HttpController implements DefaultControllerInterface
{
    use ErrorCodesLibrary;
    use IsAdmin;
    use Lang;
    use OutputBuffer;
    use Params;

    protected $controllerResult = null;
    protected $responseResolver = null;

    protected $templateDir = null;
    protected $templateName = 'main';

    protected string $level;

    public function __construct()
    {
        $this->isAdmin();

        $this->responseResolver = new ResponseResolver();
        $this->level            = ModuleConstants::getLevel();

    }

    /**
     * @param null $controllerResult
     */
    public function setControllerResult($controllerResult)
    {
        $this->controllerResult = $controllerResult;
    }


    public function runExecuteProcess($params = null)
    {
        return $this->execute($params);
    }

    //@todo refactor

    public function run($params = null)
    {
        try
        {
            \ModulesGarden\TSSGGSModule\Core\Support\Facades\Params::createFrom($params);
            $route = \ModulesGarden\TSSGGSModule\Core\Support\Facades\Router::find($this->level);
            if (!$route || !$this->hasProperContext($route->getController()))
            {
                throw new PageNotFound();
            }
            else
            {
                $this->controllerResult = (new Controller())->run($route, Request::getFacadeRoot(), function() use ($route) {
                    $action = $route->getAction();
                    return DependencyInjection::create($route->getController())->$action();
                });
            }

            $this->preResolveResponse();

            return $this->resolveResponse();
        }
        catch (PageNotFound $ex)
        {
            $this->controllerResult = new Http\PageNotFound();
            $this->preResolveResponse();

            return $this->resolveResponse();
        }
        catch (UserException $ex)
        {
            $this->controllerResult = new Http\CustomErrorPage($ex->getMessage());
            $this->preResolveResponse();

            return $this->resolveResponse();
        }
        catch (\Throwable $ex)
        {
            $this->controllerResult = (new ErrorPage())->execute(array_merge($params, ['exception' => $ex]));

            $this->preResolveResponse();

            return $this->resolveResponse();
        }
    }

    protected function preResolveResponse()
    {

    }

    protected function hasProperContext($controller): bool
    {
        return $this->level === ModuleConstants::LEVEL_ADMIN && is_subclass_of($controller, AdminAreaInterface::class)
               || $this->level === ModuleConstants::LEVEL_CLIENT && is_subclass_of($controller, ClientAreaInterface::class);
    }

    public function resolveResponse()
    {
        return $this->responseResolver->setResponse($this->controllerResult)
            ->setTemplateName($this->getTemplateName())
            ->setTemplateDir($this->getTemplateDir())
            ->setPageController($this)
            ->resolve();
    }

    public function getTemplateName()
    {
        return $this->templateName;
    }

    public function getTemplateDir()
    {
        $this->templateDir = ModuleConstants::getResourcesDir() . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'controllers';

        return $this->templateDir;
    }
}
