<?php

namespace ModulesGarden\TSSGGSModule\Core\Components\Traits;

/**
 * Trait ElementsTrait
 */
trait UrlTrait
{
    /**
     * @param string $url
     * @return \ModulesGarden\TSSGGSModule\Components\NavBarItem\NavBarItem|UrlTrait
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
