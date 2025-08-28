<?php

namespace ModulesGarden\TTSGGSModule\Core\Contracts\Components;

interface ComponentContainerInterface
{
    public function addElement($element): self;
}
