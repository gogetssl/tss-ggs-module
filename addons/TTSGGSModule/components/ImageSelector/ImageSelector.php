<?php

namespace ModulesGarden\TTSGGSModule\Components\ImageSelector;

use ModulesGarden\TTSGGSModule\Core\Components\FormFields\FormField;

class ImageSelector extends FormField
{
    public const COMPONENT = 'ImageSelector';

    public function __construct()
    {
        parent::__construct();
        $this->setTranslations(['remove_selected_image']);
    }

    public function setMediaLibrary($mediaLibrary): self
    {
        $this->setSlot("mediaLibrary", $mediaLibrary);

        return $this;
    }
}