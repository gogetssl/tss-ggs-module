<?php

namespace ModulesGarden\TTSGGSModule\Core;

use function ModulesGarden\TTSGGSModule\Core\Helper\isAdmin;

class ModuleConstants
{
    public const  DEFAULT_CONTROLLER_ACTION = 'index';

    public const CONTROLLER_ACTION_PARAMETER = 'mg-action';

    public const CONTROLLER_PAGE_PARAMETER = 'mg-page';

    public const LEVEL_ADMIN             = 'admin';
    public const LEVEL_CLIENT            = 'client';
    public const LEVEL_AUTO              = '';
    public const MODULE_TYPE_ADDON       = 'addon';
    public const MODULE_TYPE_PROVISIONIG = 'provisioning';

    protected static $mgCoreConfig = null;
    protected static $mgDevConfig = null;
    protected static $mgModuleNamespace = "ModulesGarden\TTSGGSModule";
    protected static $mgModuleRootDir = null;
    protected static $mgTemplateDir = null;
    protected static $prefixDataBase = '';
    protected static $resourcesDir = null;

    public static function getCoreConfigDir()
    {
        return self::$mgCoreConfig;
    }

    public static function getDevConfigDir()
    {
        return self::$mgDevConfig;
    }

    public static function getFullNamespace()
    {
        $fullNamespace = self::getRootNamespace();
        foreach (func_get_args() as $dir)
        {
            $fullNamespace .= ('\\' . $dir);
        }

        return $fullNamespace;
    }

    public static function getRootNamespace()
    {
        return self::$mgModuleNamespace;
    }

    public static function getFullPathWhmcs()
    {
        $fullPath = ROOTDIR;
        foreach (func_get_args() as $dir)
        {
            $fullPath .= (DIRECTORY_SEPARATOR . $dir);
        }

        return $fullPath;
    }

    public static function getPrefixDataBase()
    {
        return self::$prefixDataBase . '_';
    }

    public static function getAssetsDir(...$dir)
    {
        return self::getResourcesDir() . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $dir);
    }

    public static function getResourcesDir()
    {
        return self::$resourcesDir;
    }

    public static function getTemplateDir()
    {
        return self::$mgTemplateDir;
    }

    public static function initialize()
    {
        self::$mgModuleRootDir = dirname(__DIR__);
        self::$mgDevConfig     = self::getFullPath('app', 'Config');
        self::$mgCoreConfig    = self::getFullPath('core', 'Config');
        self::$mgTemplateDir   = self::getFullPath('resources', 'views');
        self::$resourcesDir    = self::getFullPath('resources');
        self::$prefixDataBase  = self::loadDataBasePrefix();
    }

    public static function getFullPath(...$elements)
    {
        $fullPath = self::getModuleRootDir();
        foreach ($elements as $dir)
        {
            $fullPath .= (DIRECTORY_SEPARATOR . $dir);
        }

        return $fullPath;
    }

    public static function getModuleRootDir()
    {
        return self::$mgModuleRootDir;
    }

    /**+
     * @return string
     */
    public static function loadDataBasePrefix(): string
    {
        return self::getModuleName();
    }

    /**
     * @return string
     */
    public static function getModuleName(): string
    {
        return basename(self::$mgModuleRootDir);
    }

    public static function getModuleType(): string
    {
        return basename(dirname(self::$mgModuleRootDir));
    }

    public static function getLevel(): string
    {
        return isAdmin() ? self::LEVEL_ADMIN : self::LEVEL_CLIENT;
    }
}
