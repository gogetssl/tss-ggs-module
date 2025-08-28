<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\FallbackTraits;

use ModulesGarden\TTSGGSModule\Core\Components\Actions\Redirect;


/**
 * Trait ElementsTrait
 */
trait ButtonFallbackTrait
{
    /**
     * @param string $url
     * @return $this
     * @deprecated - use onClick with Redirect
     */
    public function setRawUrl(string $url): self
    {
        $this->onClick(new Redirect($url ?? '', $this->actions['onClick']['params'] ?? []));

        return $this;
    }

    /**
     * @param array $params
     * @return $this
     * @deprecated - use onClick with Redirect
     */
    public function setRedirectParams(array $params): self
    {
        $this->onClick(new Redirect($this->actions['onClick']['url'] ?? '', $params));

        return $this;
    }
}
