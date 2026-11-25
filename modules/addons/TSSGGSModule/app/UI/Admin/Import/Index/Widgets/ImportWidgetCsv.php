<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Import\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Import\Index\Forms\ImportFormCsv;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;

class ImportWidgetCsv extends Widget
{
    public function loadHtml(): void
    {
        $this->addElement(new ImportFormCsv());
    }
}
