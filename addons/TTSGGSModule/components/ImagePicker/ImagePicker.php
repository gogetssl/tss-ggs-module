<?php

namespace ModulesGarden\TTSGGSModule\Components\ImagePicker;

use ModulesGarden\TTSGGSModule\Components\MediaLibrary\MediaLibrary;
use ModulesGarden\TTSGGSModule\Core\Components\FormFields\FormField;
use ModulesGarden\TTSGGSModule\Packages\MediaLibrary\Helpers\ContentUrlGenerator;

class ImagePicker extends FormField
{
    public const COMPONENT             = 'ImagePicker';
    public const DROPDOWN_HEIGHT_1_ROW = 'lu-image-picker-h-1-row';
    public const DROPDOWN_HEIGHT_2_ROW = 'lu-image-picker-h-2-row';
    public const DROPDOWN_HEIGHT_3_ROW = 'lu-image-picker-h-3-row';
    public const DROPDOWN_HEIGHT_4_ROW = 'lu-image-picker-h-4-row';
    public const DROPDOWN_HEIGHT_5_ROW = 'lu-image-picker-h-5-row';

    public MediaLibrary $mediaLibrary;

    public function __construct()
    {
        parent::__construct();
        $this->setTranslations(['no_image_selected']);
        $this->setDropdownHeight(self::DROPDOWN_HEIGHT_1_ROW);
    }

    public function currentImageUrlSlotBuilder(): ?string
    {
        $value = $this->getSlot('value');
        if (!$value)
        {
            return null;
        }

        return ContentUrlGenerator::generateWithParams(["fileName" => $value]);
    }

    public function setMediaLibrary(MediaLibrary $mediaLibrary): self
    {
        $this->mediaLibrary = $mediaLibrary;

        return $this;
    }

    public function disableSearching(): self
    {
        $this->setSlot('disableSearching', true);

        return $this;
    }

    public function mediaLibrarySlotBuilder(): MediaLibrary
    {
        return $this->mediaLibrary;
    }

    public function setDropdownHeight(string $dropdownHeightClass): self
    {
        $this->setSlot('dropdownHeightClass', $dropdownHeightClass);

        return $this;
    }

}
