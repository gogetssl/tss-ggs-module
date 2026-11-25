<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Import\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Import\Index\Forms\TemplateDownloadForm;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;

class TemplateDownloadWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setCss('tssggs-dl');
        $this->addElement(new TemplateDownloadForm());
    }
}
