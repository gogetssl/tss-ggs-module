<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\View;

use ModulesGarden\TTSGGSModule\Core\ModuleConstants;

class JavaScriptLoader
{
    protected const RELATED_SCRIPTS = [
        "vue-draggable"
    ];

    public function getContent(): string
    {
        $paths = [
            ModuleConstants::getFullPath('resources', 'assets', 'js', 'core'),
        ];

        $content = '';
        foreach ($paths as $path)
        {
            $files   = array_unique(glob($path . '/{,*/,*/*}*.js', GLOB_BRACE));
            $content .= $this->includeFiles($files);
        }


        return $content;
    }

    public function generateImports()
    {
        $path            = ModuleConstants::getFullPath('resources', 'assets', 'js');
        $noAutoloadDir   = ModuleConstants::getFullPath('resources', 'assets', 'js', 'custom', 'static');

        $files           = array_unique(glob($path . '/{*/,*/*/*}*.js', GLOB_BRACE));
        $noAutoloadFiles = array_unique(glob($noAutoloadDir . '/{*,*/,*/*/*}*.js', GLOB_BRACE));

        return $this->includeFiles(array_diff($files, $noAutoloadFiles));
    }

    protected function includeFiles(array $files): string
    {
        $content = '';

        usort($files, function($a, $b) {
            //if script is related by another then should be on the end
            if ($this->isRelatedScript($a)) return 1;
            if ($this->isRelatedScript($b)) return -1;
            return strcmp($a, $b);
        });

        $isDebug = false;
//        $isDebug = \ModulesGarden\TTSGGSModule\Core\Support\Facades\Config::get('configuration.debug'); //wali błędem

        $appends = [
            "vue.js" => "typeof Vue !== 'object' && typeof Vue !== 'function' && ",
            "vue.min.js" => "typeof Vue !== 'object' && typeof Vue !== 'function' && ",
        ];

        $verifiedFiles = [];
        foreach ($files as $file)
        {
            $baseName = str_replace(['.debug.js', '.min.js', '.js'], '', $file);
            $debugFileVersion = $baseName . '.debug.js';
            $minFileVersion = $baseName . '.min.js';
            $standardFileVersion = $baseName . '.js';

            $verifiedFiles[] = $isDebug ?
                //take unminified
                (in_array($standardFileVersion, $files) ? $standardFileVersion : (in_array($minFileVersion, $files) ? $minFileVersion : $debugFileVersion)) :
                //take minified
                (in_array($debugFileVersion, $files) ? $debugFileVersion : (in_array($minFileVersion, $files) ? $minFileVersion : $standardFileVersion));
        }

        $files = array_unique($verifiedFiles);

        foreach ($files as $file)
        {
            $baseFileName = basename($file);

            $allCatalogs = explode('/', $file);
            $relativeCatalogs  = array_slice($allCatalogs, (int)array_search('resources', $allCatalogs) + 1);
            $overridePath = call_user_func_array([ModuleConstants::class, 'getFullPath'], array_merge(['overrides'], $relativeCatalogs));

            $jsContent = file_exists($overridePath) ? file_get_contents($overridePath) : file_get_contents($file) ;

            if (in_array($baseFileName, array_keys($appends))) {
                $jsContent = $appends[$baseFileName] . $jsContent;
            }

            $content   .= $jsContent ?: '';
            $content   .= "\n";
        }

        return $content;
    }

    protected function isRelatedScript($file):bool
    {
        $exploded = explode('/', $file);
        $suffixed = end($exploded);

        return in_array(explode('.', $suffixed)[0], self::RELATED_SCRIPTS);
    }
}