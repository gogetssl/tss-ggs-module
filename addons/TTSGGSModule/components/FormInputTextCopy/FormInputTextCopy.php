<?php

namespace ModulesGarden\TTSGGSModule\Components\FormInputTextCopy;

use ModulesGarden\TTSGGSModule\Components\CopyToClipboardButton\CopyToClipboardButton;
use ModulesGarden\TTSGGSModule\Components\FormInputGroup\FormInputGroup;
use ModulesGarden\TTSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ValueInterface;

class FormInputTextCopy extends FormInputGroup implements ValueInterface
{
    protected FormInputText $textField;
    protected CopyToClipboardButton $copyButton;

    public function __construct()
    {
        $this->textField  = new FormInputText();
        $this->copyButton = new CopyToClipboardButton();

        parent::__construct();
    }

    public function preLoadHtml():void
    {
        $this->initFormInputText();
        $this->initCopyButton();
    }

    public function postLoadHtml():void
    {
        $this->addFormInputText();
        $this->addCopyButton();
    }

    public function setValue($value): self
    {
        $this->textField->setValue($value);

        return $this;
    }

    public function setName(string $name): self
    {
        $this->setSlot('name', $name);

        return $this;
    }

    public function getName(): string
    {
        return $this->getSlot('name');
    }

    public function setTitle(string $title): self
    {
        $this->textField->setTitle($title);

        return $this;
    }

    public function getTitle(): string
    {
        return $this->textField->getTitle();
    }

    public function setDescription(string $description): self
    {
        $this->textField->setDescription($description);

        return $this;
    }

    public function getDescription(): string
    {
        return $this->textField->getDescription();
    }

    public function setDefaultValue($defaultValue): self
    {
        $this->textField->setDefaultValue($defaultValue);

        return $this;
    }

    public function setDisabled(bool $disabled = true): self
    {
        $this->textField->setDisabled($disabled);

        return $this;
    }

    public function setPlaceholder(string $placeholder): self
    {
        $this->textField->setPlaceholder($placeholder);

        return $this;
    }

    public function setReadOnly(bool $readonly = true) : self
    {
        $this->textField->setReadOnly($readonly);

        return $this;
    }

    public function setAutocomplete(string $autocomplete) : self
    {
        $this->textField->setAutocomplete($autocomplete);

        return $this;
    }

    protected function initFormInputText():void
    {
        $this->textField->setName($this->getName());
    }

    protected function initCopyButton():void
    {
        $this->copyButton->copyFromUsingName($this->getName());;
    }


    protected function addFormInputText():void
    {
        $this->addElement($this->textField);
    }

    protected function addCopyButton():void
    {
        $this->addElement($this->copyButton);
    }
}