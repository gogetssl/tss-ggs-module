<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Overview;

use ModulesGarden\TTSGGSModule\Components\Badge\Badge;
use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Components\Grid\Grid;
use ModulesGarden\TTSGGSModule\Components\Icon\Icon;
use ModulesGarden\TTSGGSModule\Components\ListInfo\ListInfo;
use ModulesGarden\TTSGGSModule\Components\ListInfo\ListInfoItem;
use ModulesGarden\TTSGGSModule\Components\PreBlock\PreBlock;
use ModulesGarden\TTSGGSModule\Components\Text\Text;
use ModulesGarden\TTSGGSModule\Components\TextParagraph\TextParagraph;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;

class Overview extends Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $widget = new Widget();
        $widget->setTitle('Overview');
        $widget->addElement((new TextParagraph())->setText('Report Generator For WHMCS grants you the opportunity to generate and easily manage any kind of reports with inbuilt widgets. You can schedule the creation of specific reports, and afterwards send them to your staff members as well as any provided email address.'));
        $widget->addElement((new TextParagraph())->setText('Add the below cron task to allow sending reports to email or an FTP server (every 5 minutes suggested):'));
        $widget->addElement((new PreBlock())->setContent('php -q /var/www/'));
        $widget->addElement((new TextParagraph())->setText('Add the below cron task to allow executing scheduled tasks (every 24 hours suggested):'));
        $widget->addElement((new PreBlock())->setContent('php -q /var/www/'));


        //List info with icons
        $row = new \ModulesGarden\TTSGGSModule\Components\Container\Container();
        $row->addElement((new Icon())->setIcon('accounts-list-alt'));
        $row->addElement((new Text())->setText('Client\'s Passwords'));

        $row2 = new \ModulesGarden\TTSGGSModule\Components\Container\Container();
        $row2->addElement((new Icon())->setIcon('accounts-list-alt'));
        $row2->addElement((new Text())->setText('Client\'s Categories'));

        $info = new ListInfo();
        $info->addItem(new ListInfoItem($row, (new Badge())->setText(3)->setOutline()));
        $info->addItem(new ListInfoItem($row2, (new Badge())->setText(3)->setOutline()));

        $earningSummary = new Widget();
        $earningSummary->setTitle('Clients Summary');
        $earningSummary->addElement($info);

        $grid = new Grid();
        $grid->setRows([[[$widget, 8], [$earningSummary, 4]]]);

        $this->addElement($grid);
    }
}

