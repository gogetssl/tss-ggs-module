<?php

namespace ModulesGarden\TSSGGSModule\Components\ProgressBar;

use ModulesGarden\TSSGGSModule\Components\Tooltip\Tooltip;
use ModulesGarden\TSSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TSSGGSModule\Core\Components\Enums\BackgroundColor;
use ModulesGarden\TSSGGSModule\Core\Components\Enums\Size;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\TextTrait;

class ProgressBar extends AbstractComponent
{
    use TextTrait;

    public const COMPONENT = 'ProgressBar';

    public function __construct()
    {
        parent::__construct();

        $this->setSize(Size::MEDIUM);
        $this->setType(BackgroundColor::PRIMARY);
    }

    public function setFill(float $fill):self
    {
        $this->setSlot('fill', $fill);

        return $this;
    }

    public function setSize(string $size):self
    {
        $this->setSlot('size', $size);

        return $this;
    }

    public function setType(string $class):self
    {
        $this->setSlot('backgroundClass', $class);

        return $this;
    }

    public function setDescription(string $description):self
    {
        $tooltip = new Tooltip();
        $tooltip->setTitle($description);
        $this->setSlot('descriptionTooltip', $tooltip);

        return $this;
    }

    public function disableLabel(bool $disableLabel = true):self
    {
        $this->setSlot('disableLabel', $disableLabel);

        return $this;
    }

    public function enableFillMark(bool $enableFillMark = true):self
    {
        $this->setSlot('enableFillMark', $enableFillMark);

        return $this;
    }
}