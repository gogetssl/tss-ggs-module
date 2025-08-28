<?php

namespace ModulesGarden\TTSGGSModule\Core\FileReader\Reader;

use ModulesGarden\TTSGGSModule\Core\ServiceLocator;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Description of Yml
 */
class Yml extends AbstractType
{
    protected static function replaceBackslash($data)
    {
        if (is_array($data))
        {
            return array_map(self::class . '::replaceBackslash', $data);
        }
        else
        {
            return str_replace('\\\\', '\\', $data);
        }
    }

    protected function loadFile()
    {
        $return = [];
        if (file_exists($this->path . DIRECTORY_SEPARATOR . $this->file))
        {
            $return = Yaml::parse(file_get_contents($this->path . DIRECTORY_SEPARATOR . $this->file));
            $return = array_map(self::class . '::replaceBackslash', $return ?: []);
        }


        $this->data = $return;
    }
}
