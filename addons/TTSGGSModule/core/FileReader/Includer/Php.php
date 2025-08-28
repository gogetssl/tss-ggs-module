<?php

namespace ModulesGarden\TTSGGSModule\Core\FileReader\Includer;


/**
 * Description of Json
 */
class Php
{
    public function load($path, $default = [])
    {
        if (!file_exists($path))
        {
            return $default;
        }

        return (static function() use ($path) {
            return include $path;
        })();
    }
}
