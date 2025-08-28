<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Actions\Traits;

trait WithDataFromFormTrait
{
    protected ?string $withDataFromFormId = null;

    public function withDataFromFormById(string $formId):self
    {
        $this->withDataFromFormId = $formId;

        return $this;
    }
}