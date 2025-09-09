<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements;

use ModulesGarden\TSSGGSModule\Components\AppNavBar\Breadcrumb;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonBasic;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSubmitSuccess;
use ModulesGarden\TSSGGSModule\Components\Checkbox\Checkbox;
use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TSSGGSModule\Components\NavBar\NavBar;
use ModulesGarden\TSSGGSModule\Components\NavBarItem\NavBarItem;
use ModulesGarden\TSSGGSModule\Components\TableSimple\Column\Column;
use ModulesGarden\TSSGGSModule\Components\TableSimple\Record\Record;
use ModulesGarden\TSSGGSModule\Components\TableSimple\TableSimple;
use ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalLoad;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\PassAjaxData;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\Redirect;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ClientAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Storage\Resources;
use ModulesGarden\TSSGGSModule\Core\UI\Interfaces\ClientArea;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\CronTasks\CronTasks;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Forms\AutoSaveForm;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Forms\ReloadForm;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Modals\Base;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Modals\SwitchersModal;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Overview\Overview;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\ServiceDetails\ServiceDetails;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\ServiceInformation\ServiceInformation;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\SparklineGraphs\SparklinesGraphs;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\SubPages\DataTable\DataTable;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Tabs\Tabs;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Tiles\Tiles;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\TreeView\TreeView;

class Container extends \ModulesGarden\TSSGGSModule\Components\Container\Container implements AdminAreaInterface, ClientAreaInterface
{
    public function loadHtml(): void
    {
        try
        {
            $this->addElement(new Overview());
            $this->addElement(new \ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\ListInfo\ListInfo());
            $this->addElement(new ServiceInformation());
            $this->addElement(new \ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\PageDescription\PageDescription());
            $this->addElement(new CronTasks());
            $this->addElement(new ServiceDetails());
            $this->addElement(new Tiles());
            $this->addElement(new Tabs());
            $this->addElement(new SparklinesGraphs());


//            $this->switchers();
//            $this->datatable();
//            $this->containerReload();
//            $this->board();
//
//            $this->redirectButton();
//            $this->popups();
//            $this->tableSimple();
//            $this->addAutoSaveForm();
//            $this->addReloadForm();
            $this->addNavBar();
//
//
//            $this->addWidgets();
        }
        catch (\Exception $ex)
        {
            var_dump($ex->getMessage());
            exit;
        }

    }

    protected function addNavBar()
    {
        $navBar = new NavBar();
        $navBar->addItem((new NavBarItem())->setTitle($this->translate('Orders'))->setIcon('shopping-cart')->setUrl('https://google.com'));
        $navBar->addItem((new NavBarItem())->setTitle('Pricing')->setActive(true)->setIcon('money'));

        $this->addElement($navBar);
    }

    public function addAutoSaveForm()
    {
        $widget = new Widget();
        $widget->setTitle('Auto Save Form');
        $widget->addElement(new AutoSaveForm());

        $this->addElement($widget);
    }

    protected function datatable()
    {
        $table = new DataTable();
        $table->setId('datatable-1');


//        $button = new ButtonBasic();
//        $button->onClick((new ReloadById($table->getId()))->withStaticParams(['xxxx' => 121212]));

        $form = new \ModulesGarden\TSSGGSModule\Components\Form\Form();
        $form->addElement((new FormInputText())->setName('dupa'));
        $form->addElement((new ButtonSubmitSuccess())->onClick(new FormSubmit($form)));
        //$form->onSubmit((new Alert('adadawd')));
        $form->onSubmit((new PassAjaxData($table->getId())));
        $form->onSubmit((new ReloadById($table->getId())));

        $widget = new Widget();
        $widget->setTitle('Reload datatable');
        //  $widget->addElement($button);
        $widget->addElement($form);


        $this->addElement($widget);
        $this->addElement($table);
    }

    protected function containerReload()
    {
        $this->addElement(new \ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\SubPages\ContainerReload\Container());
    }

    protected function board()
    {
        $board = new \ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Boards\Board();


        $this->addElement($board);
    }

    protected function switchers()
    {
        $button = new ButtonBasic();
        $button->setTitle('Release the Switchers!');
        $button->onClick((new ModalLoad(new SwitchersModal())));

        $toolbar = new Toolbar();
        $toolbar->addElement($button);

        $widget = new Widget();
        $widget->setTitle('Test Switchers');
        $widget->addElement($toolbar);

        $this->addElement($widget);
    }

    protected function redirectButton()
    {
        $button = new ButtonBasic();
        $button->setTitle('Redirect Me!!');
        $button->onClick((new Redirect('https://google.com'))->openNewWindow());

        $toolbar = new Toolbar();
        $toolbar->addElement($button);

        $widget = new Widget();
        $widget->setTitle('Redirect action');
        $widget->addElement($toolbar);

        $this->addElement($widget);
    }

    protected function popups()
    {
        $button = new ButtonBasic();
        $button->setTitle('Click Me!');
        $button->onClick((new ModalLoad(new Base())));

        $toolbar = new Toolbar();
        $toolbar->addElement($button);

        $widget = new Widget();
        $widget->setTitle('Popups');
        $widget->addElement($toolbar);

        $this->addElement($widget);
    }

    protected function tableSimple()
    {
        $table = new TableSimple();
        $table->setRecords([
            ['Subtotal', '287', 'Total', '317', new Checkbox()],
            ['VAT', '30', 'Discount', '10', new Checkbox()]
        ]);

        $widget = new Widget();
        $widget->addElement($table);


        $domainTable = new TableSimple();
        $domainTable->setColumns([
            new Column(''),
            new Column('Domain Name'),
            new Column('Price'),
            new Column('Recurring'),
        ]);
        $domainTable->addRecord(new Record([
            new Checkbox(),
            'mytestdomain.com',
            '24',
            '1 year'
        ]));

        $domainWidget = new Widget();
        $domainWidget->setTitle('Domain');
        $domainWidget->addElement($domainTable);

        $this->addElement($widget);
        $this->addElement($domainWidget);
    }

    protected function addWidgets()
    {
        $form = new \ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Forms\Form();
        $this->addElement($form);
    }

    protected function addReloadForm()
    {
        $widget = new Widget();
        $widget->setTitle('Auto Save Form');
        $widget->addElement(new ReloadForm());

        $this->addElement($widget);
    }
}
