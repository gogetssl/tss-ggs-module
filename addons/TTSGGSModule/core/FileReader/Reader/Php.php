<?php

namespace ModulesGarden\TTSGGSModule\Core\FileReader\Reader;

/**
 * Description of Json
 */
class Php extends AbstractType
{
    protected function loadFile()
    {
        $file = $this->path . DIRECTORY_SEPARATOR . $this->file;

        if (!file_exists($file))
        {
            return null;
        }

        $this->data = (static function() use ($file) {
            return include $file;
        })();
    }
}
