<?php

namespace ModulesGarden\TTSGGSModule\Core\Translation;

use ModulesGarden\TTSGGSModule\Core\ModuleConstants;

class Locales
{
    public static function getAvailable(): array
    {
        $files   = glob(ModuleConstants::getFullPath('langs', '*.php'));
        $locales = [];
        foreach ($files as $file)
        {
            if (preg_match('/\/([a-z\.]*)\.php/', $file, $matches))
            {
                $locales[] = explode('.', $matches[1])[0];
            }
        }

        return $locales;
    }
}