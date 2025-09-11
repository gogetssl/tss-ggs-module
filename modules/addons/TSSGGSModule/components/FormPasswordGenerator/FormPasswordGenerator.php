<?php

namespace ModulesGarden\TSSGGSModule\Components\FormPasswordGenerator;

use ModulesGarden\TSSGGSModule\Components\FormInputGroup\FormInputGroup;
use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TSSGGSModule\Components\RandomStringGeneratorButton\RandomStringGeneratorButton;

/**
 * Class IconButton
 */
class FormPasswordGenerator extends FormInputGroup
{
    protected FormInputText $passwordField;
    protected RandomStringGeneratorButton $passwordGeneratorButton;

    public const COMPONENT = 'FormPasswordGenerator';

    public function __construct()
    {
        parent::__construct();

        $this->createFields();
    }

    public function setName(string $name): self
    {
        $this->passwordField->setName($name);

        return $this;
    }

    public function getName(): string
    {
        return $this->passwordField->getName();
    }

    public function setAlphabet($alphabet)
    {
        $this->passwordGeneratorButton->setAlphabet($alphabet);

        return $this;
    }

    public function enableStrengthIndicator(bool $enabled = true): self
    {
        $this->setSlot("strengthIndicatorEnabled", $enabled);

        return $this;
    }

    protected function createFields()
    {
        $this->passwordField = (new FormInputText())
            ->setName($this->getId());

        $this->passwordGeneratorButton = (new RandomStringGeneratorButton())
            ->onClickUpdateField($this->passwordField);

        $this->addElement($this->passwordField);
        $this->addElement($this->passwordGeneratorButton);
    }
}