<?php

namespace ModulesGarden\TTSGGSModule\Components\FormGroup;

use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\FormFieldInterface;

class FormGroup extends Container implements FormFieldInterface
{
    public const COMPONENT = 'FormGroup';

 

    public function getName(): string
    {
        return '';
        // TODO: Implement getName() method.
    }

    public function setError($error)
    {
        $this->setSlot('error', $error);
    }

    public function setFieldName($fieldName)
    {
        $this->setSlot('fieldName', $fieldName);
    }
}
