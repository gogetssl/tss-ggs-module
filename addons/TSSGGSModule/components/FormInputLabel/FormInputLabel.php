<?php

namespace ModulesGarden\TSSGGSModule\Components\FormInputLabel;

use ModulesGarden\TSSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\CssContainerTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\TextTrait;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\FormFieldInterface;

class FormInputLabel extends AbstractComponent implements FormFieldInterface
{
    use TextTrait;
    use CssContainerTrait;

    public const COMPONENT = 'FormInputLabel';

    public function getName(): string
    {
        return '';
    }

    public function setIcon(string $icon): self
    {
        $this->setSlot('icon', $icon);

        return $this;
    }

    public function setFor(string $for): self
    {
        $this->setSlot('for', $for);

        return $this;
    }
}