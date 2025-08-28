<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Traits;

trait OutlineTrait
{
    /**
     * @param bool $outline
     * @return $this
     */
    public function setOutline(bool $outline = true): self
    {
        $this->setSlot('outline', $outline);

        return $this;
    }
}
