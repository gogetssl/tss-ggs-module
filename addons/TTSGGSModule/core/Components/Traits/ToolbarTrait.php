<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Traits;

use ModulesGarden\TTSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ComponentInterface;

trait ToolbarTrait
{
    protected Toolbar $toolbar;

    /**
     * @param ComponentInterface $component
     * @return self
     **/
    public function addToToolbar(ComponentInterface $component): self
    {
        $this->addElementToToolbar($component);

        $this->setSlot('elements.toolbar', $this->toolbar);

        return $this;
    }

    /**
     * @param array $toolbarElements
     * @return self
     **/
    public function setToolbarElements(array $toolbarElements = []): self
    {
        foreach ($toolbarElements as $element)
        {
            $this->addElementToToolbar($element);
        }

        $this->setSlot('elements.toolbar', $this->toolbar);

        return $this;
    }

    /**
     * @param Toolbar $toolbar
     * @return self
     **/
    public function setToolbar(Toolbar $toolbar): self
    {
        $this->toolbar = $toolbar;

        $this->setSlot('elements.toolbar', $toolbar);

        return $this;
    }

    protected function addElementToToolbar(ComponentInterface $component)
    {
        if (!isset($this->toolbar))
        {
            $this->toolbar = new Toolbar();
        }

        $this->toolbar->addElement($component);
    }

    public function setToolbarLeftSided():self
    {
        if (!isset($this->toolbar))
        {
            $this->toolbar = new Toolbar();
        }

        $this->toolbar->appendCss("lu-is-left");

        return $this;
    }

    public function setToolbarRightSided():self
    {
        if (!isset($this->toolbar))
        {
            $this->toolbar = new Toolbar();
        }

        $this->toolbar->appendCss("lu-is-right");

        return $this;
    }

    public function setToolbarContentCentered():self
    {
        if (!isset($this->toolbar))
        {
            $this->toolbar = new Toolbar();
        }

        $this->toolbar->appendCss("lu-d-flex lu-justify-content-center");

        return $this;
    }
}