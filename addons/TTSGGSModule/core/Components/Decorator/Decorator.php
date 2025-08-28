<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Decorator;

class Decorator extends AbstractDecorator
{
    public function background(): Background
    {
        return new Background($this->component);
    }

    public function columns(): Columns
    {
        return new Columns($this->component);
    }

    public function childrenSize(): ChildrenSize
    {
        return new ChildrenSize($this->component);
    }

    public function font(): Font
    {
        return new Font($this->component);
    }

    public function collapse(): Collapse
    {
        return new Collapse($this->component);
    }
}