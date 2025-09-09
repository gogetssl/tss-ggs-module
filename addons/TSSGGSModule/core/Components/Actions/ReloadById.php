<?php

namespace ModulesGarden\TSSGGSModule\Core\Components\Actions;

use ModulesGarden\TSSGGSModule\Core\Components\AbstractActionInterface;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\Traits\WithDataFromFormTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\Traits\WithParamsTrait;

class ReloadById extends AbstractActionInterface
{
    use WithParamsTrait;
    use WithDataFromFormTrait;

    protected string $componentId;

    public function __construct(string $componentId)
    {
        $this->componentId = $componentId;
    }

    public function toArray(): array
    {
        return [
            'action'    => 'reload',
            'elementId' => $this->componentId,
            'slots'     => array_filter([
                'ajaxData' => $this->ajaxData,
                'withDataFromFormId' => $this->withDataFromFormId,
            ])
        ];
    }
}
