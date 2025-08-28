<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TTSGGSModule\Components\Widget\Widget;

class UpgradeWidget extends Widget
{
    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));
    }
}