<?php

namespace ModulesGarden\TTSGGSModule\Core\FileReader\Reader;

/**
 * Description of Json
 */
class Css extends AbstractType
{
    protected function loadFile()
    {
        $return = '';
        if (file_exists($this->path . DIRECTORY_SEPARATOR . $this->file))
        {
            $return = file_get_contents($this->path . DIRECTORY_SEPARATOR . $this->file);
            foreach ($this->renderData as $key => $value)
            {
                $return = str_replace("#$key#", $value, $return);
            }
        }

        $this->data = $return;
    }
}
