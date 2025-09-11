<?php

namespace ModulesGarden\TSSGGSModule\Components\MediaLibrary\Elements;

use ModulesGarden\TSSGGSModule\Components\Form\Form;


abstract class UploadForm extends Form
{
    protected const UPLOAD_ACTION = 'upload';
    protected $providerAction = self::UPLOAD_ACTION;

 
}
