<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Forms;

use ModulesGarden\TTSGGSModule\Components\AppNavBar\Breadcrumb;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonBasic;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonSubmitSuccess;
use ModulesGarden\TTSGGSModule\Components\Checkbox\Checkbox;
use ModulesGarden\TTSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TTSGGSModule\Components\NavBar\NavBar;
use ModulesGarden\TTSGGSModule\Components\NavBarItem\NavBarItem;
use ModulesGarden\TTSGGSModule\Components\TableSimple\Column\Column;
use ModulesGarden\TTSGGSModule\Components\TableSimple\Record\Record;
use ModulesGarden\TTSGGSModule\Components\TableSimple\TableSimple;
use ModulesGarden\TTSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ModalLoad;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\PassAjaxData;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\Redirect;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ClientAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Storage\Resources;
use ModulesGarden\TTSGGSModule\Core\UI\Interfaces\ClientArea;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Forms\AutoSaveForm;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Forms\ReloadForm;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Modals\Base;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Modals\SwitchersModal;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\SubPages\DataTable\DataTable;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Forms\AutoSave\AutoSave;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Forms\MarkdownEditor\MarkdownEditor;

class Container extends \ModulesGarden\TTSGGSModule\Components\Container\Container implements AdminAreaInterface, ClientAreaInterface
{
    public function loadHtml(): void
    {
        try
        {
            $this->addElement(new Forms\Forms());
            $this->addElement(new AutoSave());
            $this->addElement(new MarkdownEditor());

//            $this->switchers();
//            $this->datatable();
//            $this->containerReload();
//            $this->board();
//
//            $this->redirectButton();
//            $this->popups();
//            $this->tableSimple();
//            $this->addReloadForm();
//
//
//            $this->addWidgets();
        }
        catch (\Throwable $ex)
        {
            var_dump($ex->getMessage());
            exit;
        }

    }

    protected function datatable()
    {
        $table = new DataTable();
        $table->setId('datatable-1');


//        $button = new ButtonBasic();
//        $button->onClick((new ReloadById($table->getId()))->withStaticParams(['xxxx' => 121212]));

        $form = new \ModulesGarden\TTSGGSModule\Components\Form\Form();
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
        $this->addElement(new \ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\SubPages\ContainerReload\Container());
    }

    protected function board()
    {
        $board = new \ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Boards\Board();


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

    protected function addNavBar()
    {
        $navBar = new NavBar();
        $navBar->addItem((new NavBarItem())->setTitle($this->translate('Orders'))->setIcon('shopping-cart')->setUrl('https://google.com'));
        $navBar->addItem((new NavBarItem())->setTitle('Pricing')->setActive(true)->setIcon('money'));

        $this->addElement($navBar);
    }


    protected function addWidgets()
    {
        $form = new \ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Forms\Form();
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
