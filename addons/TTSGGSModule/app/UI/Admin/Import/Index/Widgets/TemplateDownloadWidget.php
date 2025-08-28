<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Import\Index\Widgets;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Import\Index\Forms\ImportForm;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Import\Index\Forms\TemplateDownloadForm;
use ModulesGarden\TTSGGSModule\Components\Alert\Alert;
use ModulesGarden\TTSGGSModule\Components\Label\LabelDanger;
use ModulesGarden\TTSGGSModule\Components\Link\Link;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Components\Enums\Color;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Currency;

class TemplateDownloadWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->addElement(new TemplateDownloadForm());
    }
}