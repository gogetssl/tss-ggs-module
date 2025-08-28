<?php

namespace ModulesGarden\TTSGGSModule\Components\Image;

use ModulesGarden\TTSGGSModule\Core\Components\Traits\UrlTrait;
use ModulesGarden\TTSGGSModule\Components\Container\Container;

class Image extends Container
{
    use UrlTrait;

    public const COMPONENT = 'Image';

    public function __construct()
    {
        parent::__construct();

        $this->setTranslations([
            'no_image',
        ]);
    }
}
