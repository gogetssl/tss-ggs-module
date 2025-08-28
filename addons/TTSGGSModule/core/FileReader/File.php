<?php

namespace ModulesGarden\TTSGGSModule\Core\FileReader;

/**
 * Description of File
 *

 */
class File
{
    public static function createFile()
    {
        // next version
    }

    public static function createPaths()
    {
        foreach (func_get_args() as $path)
        {
            if (is_array($path) && isset($path['permission']) && self::createPath($path['full'], $path['permission']) === false)
            {
                $parentPath = explode(DIRECTORY_SEPARATOR, $path['full']);
                array_pop($pathFull);
                $parentPath = implode(DIRECTORY_SEPARATOR, $parentPath);
                self::setPermission($parentPath);
                self::setUser($path['full'], 'www-data');
                self::createPath($path['full'], $path['permission']);
            }
            elseif (is_array($path) && self::createPath($path['full']) === false)
            {
                $parentPath = explode(DIRECTORY_SEPARATOR, $path['full']);
                array_pop($pathFull);
                $parentPath = implode(DIRECTORY_SEPARATOR, $parentPath);
                self::setPermission($parentPath);
                self::setUser($path['full'], 'www-data');
                self::createPath($path['full']);
            }
            else
            {
                self::createPath($path);
            }
        }
    }

    public static function createPath($path, $permission = 0777)
    {
        return mkdir($path, $permission);
    }

    public static function setPermission($file, $permission = 0777)
    {
        return chmod($file, $permission);
    }

    public static function setUser($file, $user)
    {
        return chown($file, $user);
    }

    /**
     * @return PathValidator
     */
    public static function getValidator()
    {
        return new PathValidator();
    }
}
