<?php

namespace ModulesGarden\TSSGGSModule\Components\DataTable\Modifiers;

use ModulesGarden\TSSGGSModule\Core\UI\Formatters\RelatedItem\RelatedItem;

class RelatedItemModifier extends RelatedItem
{
    public function __invoke($fieldName, $row, $fieldValue, $raw)
    {
        return self::formatFromData($row);
    }
}