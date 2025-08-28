<?php

namespace ModulesGarden\TTSGGSModule\Core\DataProviders;

use Exception;
use ModulesGarden\TTSGGSModule\Core\Contracts\CrudProviderInterface;
use function ModulesGarden\TTSGGSModule\Core\make;

class ProviderContainer implements CrudProviderInterface
{
    protected $provider;

    public function __construct($providerClassOrNamespace)
    {
        $this->createProvider($providerClassOrNamespace);
    }

    protected function createProvider($providerClassOrNamespace)
    {
        $provider = is_string($providerClassOrNamespace) ? make($providerClassOrNamespace) : $providerClassOrNamespace;

        if (!$provider)
        {
            throw new Exception('provider is not defined');
        }
        if (!$provider instanceof CrudProviderInterface)
        {
            throw new Exception("Provider $providerClassOrNamespace does not implement CrudProviderInterface");
        }

        $this->provider = $provider;
    }

    public function create()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @throws Exception
     */
    public function __call($method, $params)
    {
        return $this->call($method, $params);
    }

    public function call($method, $params = [])
    {
        if (!method_exists($this->provider, $method))
        {
            throw new Exception('Invalid method for provider');
        }

        return call_user_func_array([$this->provider, $method], $params);
    }

    public function delete()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getAvailableValuesById($name)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getValueById($name)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function read()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function update()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }
}
