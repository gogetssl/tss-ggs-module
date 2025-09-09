<?php

namespace ModulesGarden\TSSGGSModule\Core\UI\Formatters\RelatedItem;

interface ItemTypeInterface
{
    public function generateUrl():string;
    public function generateName():string;
}