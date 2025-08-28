<?php

namespace ModulesGarden\TTSGGSModule\Components\Slider;

use ModulesGarden\TTSGGSModule\Components\SliderMarks\SliderMarks;
use ModulesGarden\TTSGGSModule\Core\Components\Enums\Type;
use ModulesGarden\TTSGGSModule\Core\Components\FormFields\FormField;

class Slider extends FormField
{
    public const COMPONENT = 'Slider';

    public function __construct()
    {
        parent::__construct();

        $this->setType(Type::PRIMARY);
        $this->setMin(0);
        $this->setMax(100);
    }

    public function setMin($value):self
    {
        $this->setSlot('min', $value);

        return $this;
    }

    public function setMax($value):self
    {
        $this->setSlot('max', $value);

        return $this;
    }

    public function setStep($value):self
    {
        $this->setSlot('step', $value);

        return $this;
    }

    public function setType($value):self
    {
        $this->setSlot('type', $value);

        return $this;
    }

    public function setMarks(SliderMarks $sliderMarks):self
    {
        $this->setSlot('elements.marks', $sliderMarks);

        return $this;
    }

    public function enableValuePreview(bool $enableValueView = true):self
    {
        $this->setSlot('valuePreviewEnabled', $enableValueView);

        return $this;
    }

    public function setValuesMap(array $map):self
    {
        $this->setSlot('valuesMap', $map);

        return $this;
    }
}
