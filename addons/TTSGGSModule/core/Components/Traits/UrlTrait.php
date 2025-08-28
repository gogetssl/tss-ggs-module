<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Traits;

/**
 * Trait ElementsTrait
 */
trait UrlTrait
{
    /**
     * @param string $url
     * @return \ModulesGarden\TTSGGSModule\Components\NavBarItem\NavBarItem|UrlTrait
     */
    public function setUrl(?string $url): self
    {
        $this->setSlot('url', $url);

        return $this;
    }

    public function setTarget(?string $target): self
    {
        $this->setSlot('target', $target);

        return $this;
    }
}
