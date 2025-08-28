<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Traits;

/**
 * Trait ElementsTrait
 */
trait TitleTrait
{
    /**
     * @param string $title
     * @return self
     **/
    public function setTitle(string $title): self
    {
        $this->setSlot('title', $title);

        return $this;
    }
}
