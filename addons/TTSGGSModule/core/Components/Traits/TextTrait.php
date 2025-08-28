<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Traits;

trait TextTrait
{
    /**
     * @param string $text
     * @return self
     */
    public function setText(string $text): self
    {
        $this->setSlot('text', $text);

        return $this;
    }
}
