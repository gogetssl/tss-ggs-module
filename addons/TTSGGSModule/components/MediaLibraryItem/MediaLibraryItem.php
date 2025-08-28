<?php

namespace ModulesGarden\TTSGGSModule\Components\MediaLibraryItem;

use ModulesGarden\TTSGGSModule\Components\Button\Button;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ImageTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TranslatorTrait;

class MediaLibraryItem extends Button
{
    use ImageTrait;
    use TranslatorTrait;

    public const COMPONENT      = 'MediaLibraryItem';
    public const OVERLAY_ICONS  = [
        'mode_manage' => 'delete',
        'mode_select' => 'plus',
        'mode_present' => '',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->setTranslations([
            'mode_manage',
            'mode_select',
            'mode_present',
        ]);

        $this->setSlot('overlayIcons', self::OVERLAY_ICONS);
    }

    public function setImageName(string $name): self
    {
        $this->setSlot('imageName', $name);
        return $this;
    }

    public function setMode($mode): self
    {
        $this->setSlot('mode', $mode);
        return $this;
    }
}
