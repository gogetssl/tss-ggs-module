<?php

namespace ModulesGarden\TTSGGSModule\Components\TileButton;

use ModulesGarden\TTSGGSModule\Components\Button\Button;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ImageTrait;

/**
 * Class IconButton
 */
class TileButton extends Button
{
    use ImageTrait;

    public const COMPONENT = 'TileButton';

    public function setActive(bool $active = true): self
    {
        $this->setSlot('active', $active);

        return $this;
    }
}
