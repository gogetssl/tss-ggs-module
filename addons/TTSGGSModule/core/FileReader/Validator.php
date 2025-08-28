<?php

namespace ModulesGarden\TTSGGSModule\Core\FileReader;

/**
 * Description of Validator
 */
class Validator
{
    private static $instance;

    /**
     * @param string $path
     * @param string $permissionFile First digit is equal readable, second digit is equal writeable ( if 1 == need )
     */
    public function validateFile($path = "", $permissionFile = "11")
    {
        if(!file_exists($path))
        {
            mkdir($path);
        }

        foreach (str_split($permissionFile) as $key => $need)
        {
            $permission = (bool)((int)$need);
            if ($key === 0)
            {
                if ($permission === 1 && is_readable($path) === false && file_exists($path))
                {
                    return false;
                }
            }
            elseif ($key === 1)
            {
                if ($permission === 1 && is_writable($path) === false && file_exists($path))
                {
                    return false;
                }
            }
        }
        return $this->isExist($path);
    }

    /**
     * @return Validator
     */
    public static function getInstance()
    {
        if (self::$instance === null)
        {
            self::$instance = new Validator();
        }

        return self::$instance;
    }

    public function isExist($path)
    {
        return file_exists($path);
    }
}

