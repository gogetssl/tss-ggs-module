<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Traits;

use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ComponentInterface;

trait ToolbarElementsContainerTrait
{
    protected array $toolbarElements = [];

    public function addToolbarElement(ComponentInterface $component)
    {
        $this->toolbarElements[] = $component;
    }

    public function setToolbarElements(array $toolbarElements = [])
    {
        foreach ($toolbarElements as $element)
        {
            $this->addToolbarElement($element);
        }
    }

    public function getToolbarElements(): array
    {
        return $this->toolbarElements;
    }
}