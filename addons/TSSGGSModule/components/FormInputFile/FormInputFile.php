<?php

namespace ModulesGarden\TSSGGSModule\Components\FormInputFile;

use ModulesGarden\TSSGGSModule\Core\Components\FormFields\FormField;

class FormInputFile extends FormField
{
    public const COMPONENT = 'FormInputFile';
    protected const DEFAULT_PLACEHOLDER = "no_file_chosen";

    public function __construct()
    {
        parent::__construct();

        $this->setTranslations([
            self::DEFAULT_PLACEHOLDER,
        ]);

        $this->setPlaceholder(self::DEFAULT_PLACEHOLDER);
    }

    public function setMultiple($isMultiple = true): self
    {
        $this->setSlot('multiple', $isMultiple);

        return $this;
    }

    public function setAllowedFileTypes(array $types = []): self
    {
        $this->setSlot('accept', implode(', ', $types));

        return $this;
    }
}
