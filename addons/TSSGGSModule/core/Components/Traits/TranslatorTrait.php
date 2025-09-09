<?php

namespace ModulesGarden\TSSGGSModule\Core\Components\Traits;

/**
 * Trait ElementsTrait
 */
trait TranslatorTrait
{
    use \ModulesGarden\TSSGGSModule\Core\Translation\TranslatorTrait;

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
