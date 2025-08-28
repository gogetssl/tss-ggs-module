<?php

namespace ModulesGarden\TTSGGSModule\Components\ImageText;

use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TextTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\UrlTrait;

class ImageText extends Container
{
    use UrlTrait;
    use TextTrait;

    public const COMPONENT = 'ImageText';

    public function __construct()
    {
        parent::__construct();

        $this->setTranslations([
            'no_image',
        ]);
    }
}