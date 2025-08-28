<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\SubPages\ContainerReload;

use ModulesGarden\TTSGGSModule\Components\Alert\AlertInfo;
use ModulesGarden\TTSGGSModule\Components\IconButton\IconButton;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\Reload;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;

class Container extends \ModulesGarden\TTSGGSModule\Components\Container\Container implements AdminAreaInterface
{
    /**
     * @return void
     */
    public function loadHtml(): void
    {
        $shouldShow = true;
        //loadHtml method is also called in Ajax so there is no need to implement separated method in this case
        if ($shouldShow)
        {
            $widget = new Widget();
            $widget->setTitle('Close me!');

            $button = new IconButton();
            $button->onClick(new Reload($this));
            $button->setIcon('refresh');
            $widget->addToToolbar($button);

            $alert = new AlertInfo();
            $alert->setText('Click icon in the toolbar if you want to hide this section');
            $widget->addElement($alert);

            $this->addElement($widget);
        }
    }

    public function loadData(): void
    {
        $this->clearElements();
    }
}