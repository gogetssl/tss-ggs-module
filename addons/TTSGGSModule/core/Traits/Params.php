<?php

namespace ModulesGarden\TTSGGSModule\Core\Traits;

trait Params
{
    /**
     * @var array
     * params container
     */
    protected $params = [];

    public function getParam($key, $default = null)
    {
        if (isset($this->params[$key]))
        {
            return $this->params[$key];
        }

        return $default;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setParams($params = [])
    {
        if (is_array($params))
        {
            $this->params = $params;
        }

        return $this;
    }

    public function setParam($key, $value = null)
    {
        $this->params[$key] = $value;

        return $this;
    }
}
