<?php

namespace ModulesGarden\TTSGGSModule\Core\Translation;

use ModulesGarden\TTSGGSModule\Core\Events\Events\MissingTranslationDetected;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Config;
use ModulesGarden\TTSGGSModule\Core\Translation\Loader\File;
use function ModulesGarden\TTSGGSModule\Core\fire;

class Translator extends \Symfony\Component\Translation\Translator
{
    protected string $defaultDomain = 'messages';
    protected const REPLACEMENTS_PREFIX = ":";

    public function __construct()
    {
        parent::__construct((new Selector())->getLanguage());

        $this->setFallbackLocales(['english']);
        $this->addLoader('file', new File());
        foreach (Locales::getAvailable() as $locale)
        {
            $this->addResource("file", '', $locale);
        }
    }

    public function getBasedOnNamespaces(array $namespaces, string $key, array $replace = [], $locale = null, $fallback = true)
    {
        foreach ($namespaces as $namespace)
        {
            $langKey     = ($namespace ? NamespaceConverter::convert($namespace) . '.' : '') . $key;
            $translation = $this->get($langKey, $replace, $locale, $fallback);

            if ($translation !== $langKey)
            {
                return $translation;
            }
        }

        $namespaceBasedKey = $this->getBasedOnNamespace($namespaces[0], $key, $replace, $locale, $fallback);

        $results = fire(new MissingTranslationDetected($namespaceBasedKey, $this->getCatalogue($this->getLocale()), $replace));

        $customTranslation = current(array_filter($results, function ($value) {
            return is_string($value) && !empty($value);
        }));

        return $customTranslation ?: (Config::get('configuration.debug', false) ? $namespaceBasedKey : $key);
    }
    /**
     * Get translation based on provided key. You must specify full path
     * @param $key
     * @param array $replace
     * @param $locale
     * @param $fallback
     * @return mixed
     */
    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {
        return parent::trans($key, $this->addReplacementsPrefix($replace), $this->defaultDomain, empty(trim((string)$locale)) ? null : $locale);
    }

    /**
     * @param string $namespace
     * @param string $key
     * @param array $replace
     * @param $locale
     * @param $fallback
     * @return mixed
     */
    public function getBasedOnNamespace(string $namespace, string $key, array $replace = [], $locale = null, $fallback = true)
    {
        $langKey = ($namespace ? NamespaceConverter::convert($namespace) . '.' : '') . $key;

        return $this->get($langKey, $replace, $locale, $fallback);
    }

    public function setDefaultDomain(string $domain): self
    {
        $this->defaultDomain = $domain;

        return $this;
    }

    private function addReplacementsPrefix(array $replacements): array
    {
        return array_combine(
            array_map(function ($value) {
                return self::REPLACEMENTS_PREFIX . trim($value, self::REPLACEMENTS_PREFIX);
            }, array_keys($replacements)),
            array_values($replacements)
        );
    }
}
