<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ElementsListComponent\ElementsLists;

use ModulesGarden\TTSGGSModule\Components\Badge\BadgeDanger;
use ModulesGarden\TTSGGSModule\Components\Badge\BadgeInfo;
use ModulesGarden\TTSGGSModule\Components\Badge\BadgeSuccess;
use ModulesGarden\TTSGGSModule\Components\Badge\BadgeWarning;
use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Components\ElementsList\ElementsList as ElementsListComponent;
use ModulesGarden\TTSGGSModule\Components\Image\Image;
use ModulesGarden\TTSGGSModule\Components\ListInfo\ListInfo;
use ModulesGarden\TTSGGSModule\Components\ListInfo\ListInfoItem;
use ModulesGarden\TTSGGSModule\Components\PageViewWidget\PageViewWidget;
use ModulesGarden\TTSGGSModule\Components\Row\Row;
use ModulesGarden\TTSGGSModule\Components\TableSimple\Record\Record;
use ModulesGarden\TTSGGSModule\Components\TableSimple\TableSimple;
use ModulesGarden\TTSGGSModule\Components\Text\Text;
use ModulesGarden\TTSGGSModule\Components\Text\TextBold;
use ModulesGarden\TTSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\DataProviders\QueryDataProvider;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Client;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Labels\Status;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ElementsListComponent\Buttons\ButtonDelete;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ElementsListComponent\Buttons\ButtonDomain;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ElementsListComponent\Buttons\ButtonEdit;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ElementsListComponent\Buttons\ButtonSettings;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ElementsListComponent\Buttons\ButtonTime;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ElementsListComponent\Components\WordPressPageViewWidget;

class WordPressSampleList extends ElementsListComponent implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadData(): void
    {
        $clients = Client::select('tblclients.id', 'tblclients.firstname', 'lastname', 'taxexempt', 'companyname');

        $dataProv = new QueryDataProvider($clients);
        $dataProv->setColumns([
            (new \ModulesGarden\TTSGGSModule\Core\DataProviders\Column('id'))->setSearchable(true),
            (new \ModulesGarden\TTSGGSModule\Core\DataProviders\Column('firstname'))->setSearchable(true),
        ]);
        $dataProv->setDefaultSorting('tblclients.id', 'DESC');
        $this->setDataProvider($dataProv);
        $this->setAjaxData(['rand' . rand(0, 100) => 1]);
    }

    protected function buildElement($record): AbstractComponent
    {
        $pageViewWidget = new PageViewWidget();
        $pageViewWidget->setTitle($record->firstname);

        $pageViewWidget->setImage($this->getImage($record));
        $pageViewWidget->setDetails($this->getDetails($record));
        $pageViewWidget->setButtonsContainer($this->getButtonsContainer($record));

        return $pageViewWidget;
    }

    protected function getImage($record): Image
    {
        $images = [
            "../modules/addons/TTSGGSModule/resources/assets//img/logo.png",
            "templates/blend/images/logo.png",
        ];

        return (new Image())->setUrl($images[rand(0,1)]);
    }

    protected function getDetails($record): AbstractComponent
    {
        $table = new TableSimple();
        $table->addRecord(new Record([
            (new TextBold())->setText('Status'),
            "Terminated",
        ]));
        $table->addRecord(new Record([
            (new TextBold())->setText("Hostname"),
            'myestdomain.com',
        ]));
        $table->addRecord(new Record([
            (new TextBold())->setText('Memory'),
            '2GB',
        ]));
        $table->addRecord(new Record([
            (new TextBold())->setText('Backups'),
            'Enabled',
        ]));

        return $table;
    }

    protected function getButtonsContainer($record): AbstractComponent
    {
        $container = new Container();

        $bar = new Toolbar();
        $bar->addElement((new ButtonDelete()));
        $bar->addElement((new ButtonDomain()));
        $bar->addElement((new ButtonEdit()));
        $bar->addElement((new ButtonSettings()));
        $bar->addElement((new ButtonTime()));

        $container->addElement($bar);
        $container->addElement(new Row());

        return $container;
    }
}