<?php

namespace ModulesGarden\TSSGGSModule\Core\FileReader;

use Exception;
use ModulesGarden\TSSGGSModule\Core\FileReader\Reader\AbstractType;
use ModulesGarden\TSSGGSModule\Core\FileReader\Reader\Css;
use ModulesGarden\TSSGGSModule\Core\FileReader\Reader\Html;
use ModulesGarden\TSSGGSModule\Core\FileReader\Reader\Ini;
use ModulesGarden\TSSGGSModule\Core\FileReader\Reader\Js;
use ModulesGarden\TSSGGSModule\Core\FileReader\Reader\Json;
use ModulesGarden\TSSGGSModule\Core\FileReader\Reader\Php;
use ModulesGarden\TSSGGSModule\Core\FileReader\Reader\Sql;
use ModulesGarden\TSSGGSModule\Core\FileReader\Reader\Xml;
use ModulesGarden\TSSGGSModule\Core\FileReader\Reader\Yml;

/**
 * Description of Reader
 */
class Reader
{
    /**
     * @param string $file
     * @return AbstractType
     */
    public static function read($file, array $renderData = [])
    {
        $path = explode(DIRECTORY_SEPARATOR, $file);
        $file = end($path);
        array_pop($path);
        $path     = implode(DIRECTORY_SEPARATOR, $path);
        $instance = null;
        $type     = self::getType($file);

        switch ($type)
        {
            case 'xml':
                $instance = new Xml($file, $path, $renderData);
                break;
            case 'ini':
                $instance = new Ini($file, $path, $renderData);
                break;
            case 'yml':
                $instance = new Yml($file, $path, $renderData);
                break;
            case 'json':
                $instance = new Json($file, $path, $renderData);
                break;
            case 'php':
                $instance = new Php($file, $path, $renderData);
                break;
            case 'sql':
                $instance = new Sql($file, $path, $renderData);
                break;
            case 'js':
                $instance = new Js($file, $path, $renderData);
                break;
            case 'css':
                $instance = new Css($file, $path, $renderData);
                break;
            case 'html':
                $instance = new Html($file, $path, $renderData);
                break;
            default:
                throw new Exception('Can\'t read file: ' . $file);
        }

        return $instance;
    }

    private static function getType($file)
    {
        $type  = null;
        $array = explode('.', $file);
        if (is_array($array))
        {
            $type = end($array);
        }

        return strtolower($type);
    }
}
