<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\BasicElements\LabelsBadgesButtons;

use ModulesGarden\TSSGGSModule\Components\Label\Label;
use ModulesGarden\TSSGGSModule\Components\Label\LabelDanger;
use ModulesGarden\TSSGGSModule\Components\Label\LabelInfo;
use ModulesGarden\TSSGGSModule\Components\Label\LabelPrimary;
use ModulesGarden\TSSGGSModule\Components\Label\LabelSecondary;
use ModulesGarden\TSSGGSModule\Components\Label\LabelSuccess;
use ModulesGarden\TSSGGSModule\Components\Label\LabelWarning;
use ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;


class Labels extends Widget implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setTitle('Labels');

        $bar = new Toolbar();
        $bar->addElement((new LabelWarning())->setText('Label Warning'));
        $bar->addElement((new LabelSuccess())->setText('Label Success'));
        $bar->addElement((new LabelDanger())->setText('Label Danger'));
        $bar->addElement((new LabelInfo())->setText('Label Info'));
        $bar->addElement((new LabelPrimary())->setText('Label Primary'));
        $bar->addElement((new LabelSecondary())->setText('Label Secondary'));
        $bar->addElement((new Label())->setText('Label Info'));

        $bar->addElement((new LabelWarning())->setText('Label Warning')->displayAsStatusLabel());
        $bar->addElement((new LabelSuccess())->setText('Label Success')->displayAsStatusLabel());
        $bar->addElement((new LabelDanger())->setText('Label Danger')->displayAsStatusLabel());
        $bar->addElement((new LabelInfo())->setText('Label Info')->displayAsStatusLabel());
        $bar->addElement((new LabelPrimary())->setText('Label Primary')->displayAsStatusLabel());
        $bar->addElement((new LabelSecondary())->setText('Label Secondary')->displayAsStatusLabel());
        $bar->addElement((new Label())->setText('Label')->displayAsStatusLabel());

        $this->addElement($bar);
    }
}
