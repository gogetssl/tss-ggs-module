<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Import\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Import\Index\Forms\ImportForm;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Import\Index\Forms\ImportFormCsv;
use ModulesGarden\TSSGGSModule\Components\Alert\Alert;
use ModulesGarden\TSSGGSModule\Components\Label\LabelDanger;
use ModulesGarden\TSSGGSModule\Components\Link\Link;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Components\Enums\Color;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Currency;

class ImportWidgetCsv extends Widget
{
    public function loadHtml(): void
    {
        $this->addElement(new ImportFormCsv());
    }
}