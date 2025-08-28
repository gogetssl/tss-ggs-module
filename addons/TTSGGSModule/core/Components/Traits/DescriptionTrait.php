<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Traits;

trait DescriptionTrait
{
    /**
     * @param string $description
     * @return self
     **/
    public function setDescription(string $description): self
    {
        $this->setSlot('description', $description);

        return $this;
    }
}