<?php

namespace ModulesGarden\TSSGGSModule\Components\Image;

use ModulesGarden\TSSGGSModule\Core\Components\Traits\UrlTrait;
use ModulesGarden\TSSGGSModule\Components\Container\Container;

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
