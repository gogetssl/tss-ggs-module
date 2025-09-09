<?php

namespace ModulesGarden\TSSGGSModule\Core\Components\Actions;

use ModulesGarden\TSSGGSModule\Core\Components\AbstractActionInterface;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\Traits\WithDataFromFormTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\Traits\WithParamsTrait;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ComponentInterface;

class Reload extends AbstractActionInterface
{
    use WithParamsTrait;
    use WithDataFromFormTrait;

    protected ComponentInterface $element;

    /**
     * @param ComponentInterface $element
     */
    public function __construct(ComponentInterface $element)
    {
        $this->element = $element;
    }

    public function toArray(): array
    {
        return [
            'action'    => 'reload',
            'elementId' => $this->element->getId(),
            'slots'     => array_filter([
                'ajaxData' => $this->ajaxData,
                'withDataFromFormId' => $this->withDataFromFormId,
            ])
        ];
    }
}
