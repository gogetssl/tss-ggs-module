<?php

namespace ModulesGarden\TTSGGSModule\Components\Button;

class ButtonUpload extends Button
{
    protected $css = 'lu-btn';

    public function __construct()
    {
        parent::__construct();

        $this->setTitle('Upload Image');
        $this->setCss('lu-btn lu-btn--primary');
    }
}
