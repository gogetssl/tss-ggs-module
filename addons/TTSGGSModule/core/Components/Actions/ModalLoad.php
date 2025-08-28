<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Actions;

use ModulesGarden\TTSGGSModule\Components\Modal\Modal;
use ModulesGarden\TTSGGSModule\Core\Components\AbstractActionInterface;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\Traits\WithParamsTrait;
use ModulesGarden\TTSGGSModule\Core\Components\DataBuilder;

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
