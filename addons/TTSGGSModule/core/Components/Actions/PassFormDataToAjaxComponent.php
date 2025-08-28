<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Actions;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractActionInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class PassFormDataToAjaxComponent extends AbstractActionInterface
{
    protected string $sourceFormSelector;
    protected AjaxComponentInterface $targetComponent;

    /**
     * Set null if you want to submit parent form
     * @param string $sourceFormSelector
     * @param AjaxComponentInterface $targetComponent
     */
    public function __construct(string $sourceFormSelector, AjaxComponentInterface $targetComponent)
    {
        $this->sourceFormSelector = $sourceFormSelector;
        $this->targetComponent = $targetComponent;
    }

    public function toArray(): array
    {
        return [
            'action'                => 'castForm',
            'sourceFormSelector'    => $this->sourceFormSelector,
            'targetComponent'       => $this->targetComponent,
        ];
    }
}