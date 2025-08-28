<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\FallbackTraits;

/**
 * Trait ElementsTrait
 */
trait LabelFallbackTrait
{

    /**
     * @param string $title
     * @return $this
     * @deprecated - use setText
     */
    public function setTitle(string $title): self
    {
        return $this->setText($title);

        return $this;
    }

    /**
     * @param string $color
     * @return $this
     * @deprecated - use setTextColor
     */
    public function setColor(string $color): self
    {
        return $this->setTextColor($color);
    }

    public function setMessage(string $message): self
    {
        $this->setTooltip($message);

        return $this;
    }
}
