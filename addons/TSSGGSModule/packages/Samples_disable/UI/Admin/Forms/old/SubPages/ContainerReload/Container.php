<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\SubPages\ContainerReload;

use ModulesGarden\TSSGGSModule\Components\Alert\AlertInfo;
use ModulesGarden\TSSGGSModule\Components\IconButton\IconButton;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\Reload;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;

class Container extends \ModulesGarden\TSSGGSModule\Components\Container\Container implements AdminAreaInterface
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