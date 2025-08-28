<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Traits;

/**
 * Trait ElementsTrait
 */
trait TranslatorTrait
{
    use \ModulesGarden\TTSGGSModule\Core\Translation\TranslatorTrait;

    protected function setTranslations(array $translations)
    {
        $out = [];
        foreach ($translations as $val)
        {
            $out[$val] = $this->translate($val);
        }

        $this->setSlot('translations', $out);
    }
}
