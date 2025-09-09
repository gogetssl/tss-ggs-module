<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Home\MediaLibrary\Forms;

use ModulesGarden\TSSGGSModule\Components\HiddenField\HiddenField;
use ModulesGarden\TSSGGSModule\Components\MediaLibrary\RemoveForm;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Home\MediaLibrary\Providers\MediaLibraryProvider;

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
