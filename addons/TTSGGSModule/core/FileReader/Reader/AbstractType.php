<?php

namespace ModulesGarden\TTSGGSModule\Core\FileReader\Reader;

/**
 * Description of AbstractType
 */
abstract class AbstractType
{
    protected $data = [];
    protected $file = '';
    protected $path = '';
    protected $renderData = [];

    public function __construct($file, $path, $renderData = [])
    {
        $this->file       = $file;
        $this->path       = $path;
        $this->renderData = $renderData;
        $this->loadFile();
    }

    abstract protected function loadFile();

    public function get($key = null, $default = null)
    {
        if ($key == null)
        {
            return $this->data;
        }

        if ($this->isExist($key))
        {
            return $this->data[$key];
        }

        return $default;
    }

    protected function isExist($key)
    {
        if (isset($this->data[$key]) || array_key_exists($key, $this->data))
        {
            return true;
        }
    }

    public function toArray(): array
    {
        return (array)$this->data;
    }
}
