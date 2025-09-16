<?php

namespace ModulesGarden\TSSGGSModule\Core\Components\Actions;

use ModulesGarden\TSSGGSModule\Components\Form\AbstractForm;
use ModulesGarden\TSSGGSModule\Core\Components\AbstractActionInterface;

class FormSubmit extends AbstractActionInterface
{
    protected $form;
    protected ?string $customAction;
    protected ?string $sourceFormSelector;

    /**
     * Set null if you want to submit parent form
     * @param string|null $form
     * @param string|null $customAction
     */
    public function __construct(AbstractForm $form = null, ?string $customAction = null, ?string $sourceFormSelector = null)
    {
        $this->form = $form;
        $this->customAction = $customAction;
        $this->sourceFormSelector = $sourceFormSelector;
    }

    public function setCustomAction(string $customAction): self
    {
        $this->customAction = $customAction;

        return $this;
    }

    public function withSourceFormData(string $sourceFormSelector): self
    {
        $this->sourceFormSelector = $sourceFormSelector;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'action'                => 'formSubmit',
            'elementId'             => $this->form->getId(),
            'customAction'          => $this->customAction,
            'sourceFormSelector'    => $this->sourceFormSelector,
        ];
    }
}
