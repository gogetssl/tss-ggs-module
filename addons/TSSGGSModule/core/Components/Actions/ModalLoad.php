<?php

namespace ModulesGarden\TSSGGSModule\Core\Components\Actions;

use ModulesGarden\TSSGGSModule\Components\Modal\Modal;
use ModulesGarden\TSSGGSModule\Core\Components\AbstractActionInterface;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\Traits\WithParamsTrait;
use ModulesGarden\TSSGGSModule\Core\Components\DataBuilder;

class ModalLoad extends AbstractActionInterface
{
    use WithParamsTrait;

    protected Modal $modal;

    public function __construct(Modal $modal)
    {
        $this->modal = $modal;
    }

    public function toArray(): array
    {
        return [
            'action'       => 'modalLoad',
            'modal'        => (new DataBuilder($this->modal))->toArray(),
            'params'       => $this->ajaxData,
        ];
    }
}
