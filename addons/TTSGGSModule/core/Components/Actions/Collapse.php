<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Actions;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractActionInterface;
use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\Traits\WithParamsTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Decorator\Decorator;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ComponentInterface;

class Collapse extends AbstractActionInterface
{
    use WithParamsTrait;

    const DEFAULT_COLLAPSED = true;
    const DEFAULT_VISIBLE = false;

    protected ComponentInterface $element;
    protected array $visibleOnValues = [];
    protected array $collapsedOnValues = [];

    /**
     * @param ComponentInterface $element
     */
    public function __construct(AbstractComponent $element,  $defaultHide = self::DEFAULT_COLLAPSED )
    {
        $collapseDecorator = (new Decorator($element))->collapse();
        $defaultHide ? $collapseDecorator->collapsed() : $collapseDecorator->visible();
        $this->element = $element;
    }

    public function visibleOnValues(array $values = []): self
    {
        $this->visibleOnValues = $values;

        return $this;
    }

    public function collapsedOnValues(array $values = []): self
    {
        $this->collapsedOnValues = $values;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'action'            => 'collapse',
            'visibleOnValues'   => $this->visibleOnValues,
            'collapsedOnValues' => $this->collapsedOnValues,
            'elementId'         => $this->element->getId(),
            'slots'             => array_filter([
                'ajaxData' => $this->ajaxData,
            ])
        ];
    }
}