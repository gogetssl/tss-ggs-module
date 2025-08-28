<?php

namespace ModulesGarden\TTSGGSModule\Components\VisibilityWrapper;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;

class VisibilityWrapper extends AbstractComponent
{
    public const COMPONENT = 'VisibilityWrapper';
    public const EQUAL= '==';
    public const INEQUAL = '!=';

    public function __construct($element)
    {
        $this->setSlot('element', $element);
        parent::__construct();
    }

    /**
     * @param string $slotName
     * @param string $value
     * @param string $operator
     * @return self
     */
    public function disableWhen(string $slotName, string $value, string $operator = self::EQUAL): self
    {
        $this->setSlot('autoDisableFieldName', $slotName);
        $this->setSlot('autoDisableFieldValue', $value);
        $this->setSlot('autoDisableOperator', $operator);

        return $this;
    }

    /**
     * @param string $slotName
     * @param string $value
     * @param string $operator
     * @return self
     */
    public function hideWhen(string $slotName, string $value, string $operator = self::EQUAL): self
    {
        $this->setSlot('autoHideFieldName', $slotName);
        $this->setSlot('autoHideFieldValue', $value);
        $this->setSlot('autoHideOperator', $operator);

        return $this;
    }
}