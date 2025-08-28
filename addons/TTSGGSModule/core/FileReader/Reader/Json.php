<?php

namespace ModulesGarden\TTSGGSModule\Core\FileReader\Reader;

/**
 * Description of Json
 */
class Json extends AbstractType
{
    protected function loadFile()
    {
        $return = [];
        if (file_exists($this->path . DIRECTORY_SEPARATOR . $this->file))
        {
            $readFile = file_get_contents($this->path . DIRECTORY_SEPARATOR . $this->file);
            $return   = json_decode($readFile, true);
        }

        $this->data = $return;
    }
}
