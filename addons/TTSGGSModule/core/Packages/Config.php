<?php

namespace ModulesGarden\TTSGGSModule\Core\Packages;

use Exception;
use ModulesGarden\TTSGGSModule\Core\Data\Container;

class Config extends Container
{
    public function __construct(string $file)
    {
        $this->loadConfig($file);
    }

    /**
     * @throws Exception
     */
    protected function loadConfig(string $file): void
    {
        $config = (static function() use ($file) {
            return include $file;
        })();

        if (!$config || !is_array($config))
        {
            throw new Exception('Invalid config in: ' . $file);
        }

        $this->createFrom($config);
    }
}
