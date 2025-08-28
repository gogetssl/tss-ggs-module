<?php

namespace ModulesGarden\TTSGGSModule\Core\Translation\Loader;

use ModulesGarden\TTSGGSModule\Core\FileReader\Reader;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\Support\Arr;


/**
 * @todo - rewrite it to separated loaders
 */
class File extends \Symfony\Component\Translation\Loader\ArrayLoader implements \Symfony\Component\Translation\Loader\LoaderInterface
{
    public function load($resource, $locale, $domain = "")
    {
        $translations = $this->searchForLanguageFiles($locale);


        return parent::load($translations, $locale, $domain);
    }

    protected function searchForLanguageFiles($locale): array
    {
        $packages   = glob(ModuleConstants::getFullPath('packages', '*', 'langs', $locale . '.php'));
        $components = glob(ModuleConstants::getFullPath('components', '*', 'langs', $locale . '.php'));
        $fragments = glob(ModuleConstants::getFullPath('fragments', '*', 'langs', $locale . '.php'));

        $default    = [
            ModuleConstants::getFullPath('langs', $locale . '.php'),
            ModuleConstants::getFullPath('langs', 'english.core.php'),
            ModuleConstants::getFullPath('overrides', 'langs', $locale . '.php'),
        ];
        $files      = array_merge($packages, $components, $fragments, $default);

        $langs = [];
        foreach ($files as $file)
        {
            if (!file_exists($file))
            {
                continue;
            }

            $translations = Arr::dot((static function() use ($file) {
                $trs = include $file;
                if ($trs && is_array($trs))
                {
                    return (array)$trs;
                }
                return (array)$_LANG;
            })());

            $langs = array_merge($langs, $translations);
        }

        //overrides
        $file = Reader::read(ModuleConstants::getFullPath('langs', $locale . '.fallback.php'));
        foreach (Arr::dot($file->toArray()) as $oldFormat => $newFormat)
        {
            $langs[$newFormat] = Arr::get($langs, $oldFormat, '');
        }

        //replace double color. Fallback support for old langs
        foreach ($langs as &$lang)
        {
            $lang = preg_replace('/\w?(\:[a-zA-Z_]*)\:\w?/', '$1', $lang);
        }

        return $langs;
    }
}
