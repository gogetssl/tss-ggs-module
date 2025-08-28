<?php

namespace ModulesGarden\TTSGGSModule\Core\FileReader\Reader;

use Piwik\Ini\IniReader;
use Piwik\Ini\IniReadingException;

/**
 * Description of Ini
 */
class Ini extends AbstractType
{
    protected function loadFile()
    {
        $return = [];
        $reader = new IniReader();
        if (file_exists($this->path . DIRECTORY_SEPARATOR . $this->file))
        {
            $return = $reader->readFile($this->path . DIRECTORY_SEPARATOR . $this->file);
        }

        $this->data = $return;
    }
}
