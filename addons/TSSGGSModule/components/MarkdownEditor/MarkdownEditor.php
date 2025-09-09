<?php

namespace ModulesGarden\TSSGGSModule\Components\MarkdownEditor;

use ModulesGarden\TSSGGSModule\Core\Components\FormFields\FormField;

/**
 * Class IconButton
 */
class MarkdownEditor extends FormField
{
    public const COMPONENT = 'MarkdownEditor';

    public function enableAutoSave(bool $autoSave = true):self
    {
        $this->setSlot('autoSaveEnabled', $autoSave);

        return $this;
    }
}
