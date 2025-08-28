<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Formatters\RelatedItem;

interface ItemTypeInterface
{
    public function generateUrl():string;
    public function generateName():string;
}