<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Actions;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractActionInterface;
use ModulesGarden\TTSGGSModule\Core\Components\DataBuilder;

class ModalOpen extends AbstractActionInterface
{
    protected $modal;

    public function __construct($modal)
    {
        $this->modal = $modal;
    }

    public function toArray(): array
    {
        return [
            'action' => 'modalOpen',
            'modal'  => (new DataBuilder($this->modal))
                ->withHtml()
                ->toArray(),
        ];
    }
}
