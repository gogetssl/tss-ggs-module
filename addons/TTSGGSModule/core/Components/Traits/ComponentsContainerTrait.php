<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Traits;

/**
 * Trait ElementsTrait
 */
trait ComponentsContainerTrait
{
    public function addElement($element): self
    {
        $this->addComponent('elements', $element);

        return $this;
    }

    public function clearElements(): self
    {
        $this->setSlot('elements', []);

        return $this;
    }

    /**
     * @param $type
     * @param $element
     */
    protected function addComponent($type, $element): self
    {
        $this->pushToSlot('elements.' . $type, $element);

        return $this;
    }

    /**
     * @return string
     */
    protected function elementsSlotBuilder()
    {
        return $this->getSlot('elements');
    }
}
