<?php

namespace ModulesGarden\TTSGGSModule\Components\CopyToClipboardButton;

use ModulesGarden\TTSGGSModule\Components\IconButton\IconButton;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TranslatorTrait;

class CopyToClipboardButton extends IconButton
{
    use TranslatorTrait;

    public const COMPONENT = 'CopyToClipboardButton';

    public function __construct()
    {
        parent::__construct();

        $this->setIcon('content-copy');
        $this->setType(self::TYPE_BUTTON);
        $this->setTranslations([
            'title',
            'text_copied',
        ]);
    }

    public function copyFromUsingId($fieldId)
    {
        $this->setSlot('copySelector', $fieldId);
        $this->setSlot('copyType', 'id');
    }

    public function copyFromUsingName($fieldName)
    {
        $this->setSlot('copySelector', $fieldName);
        $this->setSlot('copyType', 'fieldname');
    }
}
