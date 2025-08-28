<?php

namespace ModulesGarden\TTSGGSModule\Core\Api;

use AltoRouter;
use Exception;
use ModulesGarden\TTSGGSModule\App\Libs\Exceptions\ApiException;
use ModulesGarden\TTSGGSModule\App\Libs\Exceptions\WhmcsApiException;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\Traits\IsDebugOn;

/**
 * Description of Http
 * @todo
 */
class Http
{
    use IsDebugOn;

    /**
     * @var AltoRouter;
     */
    protected $router;

    public function __construct($basepath)
    {
        $this->loadRouter($basepath);
        $this->router->addMatchTypes(['d' => '[^/]+']);
    }

    /**
     * Load router object
     *
     * @param $basePath
     * @throws Exception
     */
    protected function loadRouter($basePath)
    {
        $this->router = new AltoRouter();
        $this->router->setBasePath($basePath);

        $routes = require ModuleConstants::getDevConfigDir() . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'routes.php';
        $this->router->addRoutes($routes);
    }

    /**
     * Parse API request
     */
    public function run()
    {
        try
        {
            $logger = $this->getLoggerObject();
            $match  = $this->router->match();
            if ($match)
            {
                $auth = $this->getAuthObject();
                $auth->run($match['name']);

                $validator = $this->getValidatorObject();
                $validator->run($match['name']);

                $request = explode('#', $match['target']);
                $action  = [$this->getController($request[0]), $request[1]];
                $result  = call_user_func_array($action, $match['params']);

                $logger->logInfo($match['name'], array_merge($match['params'], $_REQUEST), $result);

                echo json_encode($result);
            }
            else
            {
                header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
                echo json_encode(['error' => 'Action not found']);
            }

            exit;
        }
        catch (ApiException $mgex)
        {
            $code   = $mgex->getMgHttpCode();
            $exdata = $mgex->getAdditionalData();

            $message = "{$mgex->getMgMessage(false)}" . ($this->isDebugOn() ? ' | ' . print_r($exdata, true) : '');
        }
        catch (WhmcsApiException $whmcsex)
        {
            $exdata  = $whmcsex->getAdditionalData();
            $message = "{$exdata['data']['result']['message']}: {$exdata['data']['result']['error']}";
        }
        catch (Exception $ex)
        {
            $exdata  = $this->isDebugOn() ? print_r($ex, true) : null;
            $message = 'Please contact administration (server side issue)' . ($exdata ? ' | ' . $exdata : '');
        }

        $logger->logError($match['name'], array_merge($match['params'], $_REQUEST), $exdata);

        $response = $this->getResponseBuilderObject();
        $message  = $response->build($match['name'], $message);

        http_response_code($code ?: 500);
        echo json_encode(['error' => $message]);
    }

    /**
     * Get Logger class object
     *
     * @return Logger class object
     */
    protected function getLoggerObject()
    {
        $config = $this->getConfigElement('logger');
        $auth   = new $config['class'];

        return $auth;
    }

    /**
     * Get configuration element by type
     *
     * @param $type
     * @return mixed
     */
    protected function getConfigElement($type)
    {
        $config = require ModuleConstants::getDevConfigDir() . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'config.php';
        foreach ($config as $element)
        {
            if ($element['type'] == $type)
            {
                return $element;
            }
        }
    }

    /**
     * Get Authorization class object
     *
     * @return Auth class object
     */
    protected function getAuthObject()
    {
        $config = $this->getConfigElement('auth');
        $auth   = new $config['class'];

        return $auth;
    }

    /**
     * @return mixed
     */
    protected function getValidatorObject()
    {
        $config    = $this->getConfigElement('validator');
        $validator = new $config['class'];

        return $validator;
    }

    /**
     * Get controller object
     *
     * @return object
     */
    protected function getController($classname)
    {
        $classname = "\\ModulesGarden\\TTSGGSModule\\App\\Http\\Api\\{$classname}";

        return new $classname;
    }

    /**
     * Get Logger class object
     *
     * @return Logger class object
     */
    protected function getResponseBuilderObject()
    {
        $config = $this->getConfigElement('responseBuilder');
        $auth   = new $config['class'];

        return $auth;
    }
}
