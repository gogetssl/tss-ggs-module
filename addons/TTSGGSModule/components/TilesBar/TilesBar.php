<?php

namespace ModulesGarden\TTSGGSModule\Components\TilesBar;

use ModulesGarden\TTSGGSModule\Components\TileButton\TileButton;
use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\AjaxTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ComponentContainerInterface;

class TilesBar extends AbstractComponent implements ComponentContainerInterface
{
    use AjaxTrait;
    use ComponentsContainerTrait;

    public const COMPONENT = 'TilesBar';

    public function addTile(TileButton $tile): self
    {
        $this->addElement($tile);

        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->setSlot('title', $title);

        return $this;
    }

    public function setTitleTextCentered(bool $titleTextCentered = true): self
    {
        $this->setSlot('titleTextCentered', $titleTextCentered);

        return $this;
    }
}
