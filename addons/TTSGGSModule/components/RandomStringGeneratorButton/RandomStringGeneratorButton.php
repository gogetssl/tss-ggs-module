<?php

namespace ModulesGarden\TTSGGSModule\Components\RandomStringGeneratorButton;

use ModulesGarden\TTSGGSModule\Components\Button\Button;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ComponentInterface;

class RandomStringGeneratorButton extends Button
{
    public const COMPONENT = 'RandomStringGeneratorButton';
    protected $defaultAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+{}[];:"<>,./?';
    protected $defaultLength = 10;

    public function __construct()
    {
        parent::__construct();

        $this->setTitle($this->translate("generate"));

        $this->setAlphabet($this->defaultAlphabet);
        $this->setLength($this->defaultLength);
    }

    public function setAlphabet($alphabet): self
    {
        $this->setSlot('alphabet', $alphabet);

        return $this;
    }

    public function setLength(int $length): self
    {
        $this->setSlot('length', $length);

        return $this;
    }

    public function onClickUpdateField(ComponentInterface $component): self
    {
        $this->setSlot('fieldToUpdate', $component->getId());

        return $this;
    }
}
