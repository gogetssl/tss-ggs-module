<?php

namespace ModulesGarden\TTSGGSModule\Components\MediaLibrary\Elements;

use ModulesGarden\TTSGGSModule\Components\Form\Form;


abstract class UploadForm extends Form
{
    protected const UPLOAD_ACTION = 'upload';
    protected $providerAction = self::UPLOAD_ACTION;

 
}
