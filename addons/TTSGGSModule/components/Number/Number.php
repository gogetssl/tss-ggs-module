<?php

namespace ModulesGarden\TTSGGSModule\Components\Number;

use ModulesGarden\TTSGGSModule\Core\Components\FormFields\FormField;

/**
 * Class IconButton
 */
class Number extends FormField
{
    public const COMPONENT = 'Number';

    public function setRange(int $min, int $max): self
    {
        $this->setMin($min);
        $this->setMax($max);

        return $this;
    }

    public function setMin(int $min): self
    {
        $this->setSlot('min', $min);

        return $this;
    }

    public function setMax(int $max): self
    {
        $this->setSlot('max', $max);

        return $this;
    }

    public function setStep(int $step): self
    {
        $this->setSlot('step', $step);

        return $this;
    }
}
