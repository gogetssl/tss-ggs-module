<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Actions\Traits;

trait WithParamsTrait
{
    protected ?array $ajaxData = null;

    /**
     *
     * @param array $params
     */
    public function withParams(array $params): self
    {
        $this->ajaxData = $params;

        return $this;
    }
}