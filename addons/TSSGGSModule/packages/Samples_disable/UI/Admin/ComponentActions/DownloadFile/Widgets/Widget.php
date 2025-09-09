<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComponentActions\DownloadFile\Widgets;

use ModulesGarden\TSSGGSModule\Components\Button\ButtonSubmitSuccess;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\DownloadFileFromForm;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComponentActions\DownloadFile\Forms\Form;

class Widget extends \ModulesGarden\TSSGGSModule\Components\Widget\Widget
{
    public function loadHtml(): void
    {
        $this->setTitle('Download file');

        $button = new ButtonSubmitSuccess();
        $button->onClick(new DownloadFileFromForm(new Form(), [
            'downloadThisFile' => 'ThisFile!'
        ]));
        $button->setTitle('Download');
        $this->addElement($button);
    }
}