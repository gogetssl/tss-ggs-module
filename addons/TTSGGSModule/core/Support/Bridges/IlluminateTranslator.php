<?php

namespace ModulesGarden\TTSGGSModule\Core\Support\Bridges;

use ModulesGarden\TTSGGSModule\Core\Translation\Translator;

class IlluminateTranslator implements \Illuminate\Contracts\Translation\Translator
{
    protected Translator $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function get($key, array $replace = [], $locale = null)
    {
        return $this->translator->get($key, $replace, null);
    }

    public function choice($key, $number, array $replace = [], $locale = null)
    {

    }
    public function getLocale()
    {
        $this->translator->getLocale();
    }

    public function setLocale($locale)
    {

    }
}