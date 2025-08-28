<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\BasicElements\Alerts;

use ModulesGarden\TTSGGSModule\Components\Alert\AlertDanger;
use ModulesGarden\TTSGGSModule\Components\Alert\AlertInfo;
use ModulesGarden\TTSGGSModule\Components\Alert\AlertSuccess;
use ModulesGarden\TTSGGSModule\Components\Alert\AlertWarning;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;


class Alerts extends \ModulesGarden\TTSGGSModule\Components\Container\Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $widget = new Widget();
        $widget->setTitle('Alerts');

        $widget->addElement((new AlertDanger())->setText('Alert Danger'));
        $widget->addElement((new AlertInfo())->setText('Alert Info'));
        $widget->addElement((new AlertSuccess())->setText('Alert Success'));
        $widget->addElement((new AlertWarning())->setText('Alert Warning'));

        $widget->addElement((new AlertDanger())->setText($this->translate('Alert Danger with outline'))->setOutline());
        $widget->addElement((new AlertInfo())->setText('Alert Info with outline')->setOutline());
        $widget->addElement((new AlertSuccess())->setText('Alert Success with outline')->setOutline());
        $widget->addElement((new AlertWarning())->setText('Alert Warning with outline')->setOutline());

        $widget->addElement((new AlertDanger())->setText('Alert Danger with dismiss button')->showDismissButton());
        $widget->addElement((new AlertInfo())->setText('Alert Info with dismiss button')->showDismissButton());
        $widget->addElement((new AlertSuccess())->setText('Alert Success with dismiss button')->showDismissButton());
        $widget->addElement((new AlertWarning())->setText('Alert Warning with dismiss button')->showDismissButton());

        $widget->addElement((new AlertDanger())->setText('Alert Danger with outline and dismiss button')->setOutline()->showDismissButton());
        $widget->addElement((new AlertInfo())->setText('Alert Info with outline and dismiss button')->setOutline()->showDismissButton());
        $widget->addElement((new AlertSuccess())->setText('Alert Success with outline and dismiss button')->setOutline()->showDismissButton());
        $widget->addElement((new AlertWarning())->setText('Alert Warning with outline and dismiss button')->setOutline()->showDismissButton());

        $this->addElement($widget);
    }
}
