<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Import\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Import\Index\Forms\ImportForm;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;

class ImportWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->addElement(new ImportForm());
    }
}
