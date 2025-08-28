<?php

namespace ModulesGarden\TTSGGSModule\Components\DataTable\Modifiers;

use ModulesGarden\TTSGGSModule\Core\UI\Formatters\RelatedItem\RelatedItem;

class RelatedItemModifier extends RelatedItem
{
    public function __invoke($fieldName, $row, $fieldValue, $raw)
    {
        return self::formatFromData($row);
    }
}