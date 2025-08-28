<?php

namespace ModulesGarden\TTSGGSModule\Core\Hook;

class HookManager
{
    protected static $currentName = '';
    /**
     * @var Config
     */
    protected $config;
    protected $dir;
    protected $files = [];
    protected $hookRegister = [];

    public function __construct($dir = null)
    {
        $this->config = new Config();
        $this->dir    = $dir;
    }

    public static function create($dir, $isPackage = false)
    {
        $hooksDir    = $dir . DIRECTORY_SEPARATOR . ($isPackage ? 'Hooks' : DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Hooks') . DIRECTORY_SEPARATOR;
        $hookManager = new self($hooksDir);

        foreach ($hookManager->getFiles() as $file)
        {
            $path              = $hooksDir . $file;
            self::$currentName = explode('.', $file)[0];

            require $path;
        }


        $hookManager->start();

        return $hookManager;
    }

    public function getFiles()
    {
        $files = scandir($this->dir, 1);

        if (count($files) != 0)
        {
            foreach ($files as $key => &$value)
            {
                if ($value === '.' || $value === '..' || is_dir($this->dir . DIRECTORY_SEPARATOR . $value))
                {
                    unset($files[$key]);
                }
            }
        }

        return $files;
    }

    protected function start()
    {
        foreach ($this->hookRegister as $hook)
        {
            if ($this->config->checkHook($hook['name']))
            {
                add_hook($hook['name'], $hook['sort'], $hook['function']);
            }
        }
    }

    public function register($callback, $sort = 1)
    {
        $this->hookRegister[] = [
            'name'     => self::$currentName,
            'function' => $callback,
            'sort'     => $sort,
        ];
    }
}
