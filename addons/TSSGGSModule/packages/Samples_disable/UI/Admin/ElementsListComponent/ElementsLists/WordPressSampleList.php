<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ElementsListComponent\ElementsLists;

use ModulesGarden\TSSGGSModule\Components\Badge\BadgeDanger;
use ModulesGarden\TSSGGSModule\Components\Badge\BadgeInfo;
use ModulesGarden\TSSGGSModule\Components\Badge\BadgeSuccess;
use ModulesGarden\TSSGGSModule\Components\Badge\BadgeWarning;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\ElementsList\ElementsList as ElementsListComponent;
use ModulesGarden\TSSGGSModule\Components\Image\Image;
use ModulesGarden\TSSGGSModule\Components\ListInfo\ListInfo;
use ModulesGarden\TSSGGSModule\Components\ListInfo\ListInfoItem;
use ModulesGarden\TSSGGSModule\Components\PageViewWidget\PageViewWidget;
use ModulesGarden\TSSGGSModule\Components\Row\Row;
use ModulesGarden\TSSGGSModule\Components\TableSimple\Record\Record;
use ModulesGarden\TSSGGSModule\Components\TableSimple\TableSimple;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Text\TextBold;
use ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TSSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\DataProviders\QueryDataProvider;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Client;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Labels\Status;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ElementsListComponent\Buttons\ButtonDelete;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ElementsListComponent\Buttons\ButtonDomain;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ElementsListComponent\Buttons\ButtonEdit;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ElementsListComponent\Buttons\ButtonSettings;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ElementsListComponent\Buttons\ButtonTime;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ElementsListComponent\Components\WordPressPageViewWidget;

class WordPressSampleList extends ElementsListComponent implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadData(): void
    {
        $clients = Client::select('tblclients.id', 'tblclients.firstname', 'lastname', 'taxexempt', 'companyname');

        $dataProv = new QueryDataProvider($clients);
        $dataProv->setColumns([
            (new \ModulesGarden\TSSGGSModule\Core\DataProviders\Column('id'))->setSearchable(true),
            (new \ModulesGarden\TSSGGSModule\Core\DataProviders\Column('firstname'))->setSearchable(true),
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
            "../modules/addons/TSSGGSModule/resources/assets//img/logo.png",
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