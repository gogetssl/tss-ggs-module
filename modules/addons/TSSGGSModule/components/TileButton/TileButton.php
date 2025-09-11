<?php

namespace ModulesGarden\TSSGGSModule\Components\TileButton;

use ModulesGarden\TSSGGSModule\Components\Button\Button;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ImageTrait;

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
