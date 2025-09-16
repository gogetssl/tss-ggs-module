<?php

namespace ModulesGarden\TSSGGSModule\Core\Contracts\Components;

interface ComponentContainerInterface
{
    public function addElement($element): self;
}
