<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComponentActions\DownloadFile\Widgets;

use ModulesGarden\TTSGGSModule\Components\Button\ButtonSubmitSuccess;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\DownloadFileFromForm;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComponentActions\DownloadFile\Forms\Form;

class Widget extends \ModulesGarden\TTSGGSModule\Components\Widget\Widget
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