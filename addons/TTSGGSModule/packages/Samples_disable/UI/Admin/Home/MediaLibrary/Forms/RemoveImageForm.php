<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Home\MediaLibrary\Forms;

use ModulesGarden\TTSGGSModule\Components\HiddenField\HiddenField;
use ModulesGarden\TTSGGSModule\Components\MediaLibrary\RemoveForm;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Home\MediaLibrary\Providers\MediaLibraryProvider;

class RemoveImageForm extends RemoveForm
{
    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->setProvider(new MediaLibraryProvider());
        $this->addField((new HiddenField('id')));

        $this->provider->read();

    }
}
