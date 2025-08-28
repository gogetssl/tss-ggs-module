<?php

namespace ModulesGarden\TTSGGSModule\Components\AccordionElement;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\Enums\Color;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TextTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TitleTrait;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ComponentContainerInterface;

class AccordionElement extends AbstractComponent implements ComponentContainerInterface
{
    use TextTrait;
    use TitleTrait;
    use ComponentsContainerTrait;

    public const COMPONENT = 'AccordionElement';

    public function __construct()
    {
        parent::__construct();

        $this->setType(Color::DEFAULT);
    }

    public function setType(string $type)
    {
        $this->setSlot('type', $type);

        return $this;
    }

    public function removeIcon()
    {
        $this->setSlot('removeIcon', true);

        return $this;
    }

    public function setTextCentered(bool $textCentered = true): self
    {
        $this->setSlot('textCentered', $textCentered);

        return $this;
    }
}