<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Pages;

use ModulesGarden\TTSGGSModule\Components\Alert\AlertInfo;
use ModulesGarden\TTSGGSModule\Components\AppNavBar\Breadcrumb;
use ModulesGarden\TTSGGSModule\Components\Badge\Badge;
use ModulesGarden\TTSGGSModule\Components\Badge\BadgeDanger;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonBasic;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonSubmitSuccess;
use ModulesGarden\TTSGGSModule\Components\Checkbox\Checkbox;
use ModulesGarden\TTSGGSModule\Components\FormGroup\FormGroup;
use ModulesGarden\TTSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TTSGGSModule\Components\FormLabel\FormLabel;
use ModulesGarden\TTSGGSModule\Components\Grid\Grid;
use ModulesGarden\TTSGGSModule\Components\Icon\Icon;
use ModulesGarden\TTSGGSModule\Components\IconButton\IconButtonDelete;
use ModulesGarden\TTSGGSModule\Components\IconButton\IconButtonEdit;
use ModulesGarden\TTSGGSModule\Components\Link\Link;
use ModulesGarden\TTSGGSModule\Components\ListInfo\ListInfo;
use ModulesGarden\TTSGGSModule\Components\ListInfo\ListInfoItem;
use ModulesGarden\TTSGGSModule\Components\NavBar\NavBar;
use ModulesGarden\TTSGGSModule\Components\NavBarItem\NavBarItem;
use ModulesGarden\TTSGGSModule\Components\PageDescription\PageDescription;
use ModulesGarden\TTSGGSModule\Components\PreBlock\PreBlock;
use ModulesGarden\TTSGGSModule\Components\TableSimple\Column\Column;
use ModulesGarden\TTSGGSModule\Components\TableSimple\Record\Record;
use ModulesGarden\TTSGGSModule\Components\TableSimple\TableSimple;
use ModulesGarden\TTSGGSModule\Components\Text\Text;
use ModulesGarden\TTSGGSModule\Components\TextParagraph\TextParagraph;
use ModulesGarden\TTSGGSModule\Components\TextShowHide\TextShowHide;
use ModulesGarden\TTSGGSModule\Components\TileButton\TileButton;
use ModulesGarden\TTSGGSModule\Components\TilesBar\TilesBar;
use ModulesGarden\TTSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TTSGGSModule\Components\Tooltip\Tooltip;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ModalLoad;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\PassAjaxData;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\Redirect;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ClientAreaInterface;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\Storage\Resources;
use ModulesGarden\TTSGGSModule\Core\UI\Interfaces\ClientArea;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Forms\AutoSaveForm;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Forms\ReloadForm;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Labels\Status;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Modals\Base;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Modals\SwitchersModal;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\SubPages\DataTable\DataTable;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Modals\UserDelete;

class Container extends \ModulesGarden\TTSGGSModule\Components\Container\Container implements AdminAreaInterface, ClientAreaInterface
{
    public function loadHtml(): void
    {
        try
        {
            $this->switchers();
            $this->datatable();
            $this->containerReload();
            $this->board();
            $this->overview();

            $this->redirectButton();
            $this->popups();
            $this->cronTaskInformation();
            $this->invoiceAndEarningSummary();
            $this->serviceDetails();
            $this->tableSimple();
            $this->addAutoSaveForm();
            $this->addReloadForm();
            $this->addNavBar();
            $this->addPageInfo();
            $this->addInfo();
            $this->addSimpleTable();
            $this->addTiles();
            $this->addWidgets();
        }
        catch (\Exception $ex)
        {
            var_dump($ex->getMessage());
            exit;
        }

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

    protected function overview()
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

    protected function cronTaskInformation()
    {
        $alert = new AlertInfo();
        $alert->setText('Cron has not been started yet');

        $label = new FormLabel();
        $label->setText('Billing Data Collexting Cron');

        $tooltip = new Tooltip();
        $tooltip->setTitle('some tooltip');

        $formGroup = new FormGroup();
        $formGroup->addElement($label);
        $formGroup->addElement($tooltip);

        $collectionCron = new PreBlock();
        $collectionCron->setContent(" 0 22 * * * php -q cron.php billing");

        $label2 = new FormLabel();
        $label2->setText('Invoicing Cron');

        $tooltip2 = new Tooltip();
        $tooltip2->setTitle('some tooltip');

        $formGroup2 = new FormGroup();
        $formGroup2->addElement($label2);
        $formGroup2->addElement($tooltip2);

        $collectionCron2 = new PreBlock();
        $collectionCron2->setContent(" 0 22 * * * php -q cron.php invoice");

        $widget = new Widget();
        $widget->setTitle('Cron Tasks');
        $widget->addElement($alert);
        $widget->addElement($formGroup);
        $widget->addElement($collectionCron);
        $widget->addElement($formGroup2);
        $widget->addElement($collectionCron2);

        $this->addElement($widget);
    }

    protected function invoiceAndEarningSummary()
    {
        //Invoice summary
        $info = new ListInfo();
        $info->addItem(new ListInfoItem('Invoice Paid Last Week', (new Badge())->setText(3)->setOutline()));
        $info->addItem(new ListInfoItem('Invoice Paid Last Month', (new Badge())->setText(5)->setOutline()));
        $info->addItem(new ListInfoItem('Invoice Paid Last Quarter', (new Badge())->setText(6)->setOutline()));
        $info->addItem(new ListInfoItem('Invoice Paid Year', (new Badge())->setText(1221)->setOutline()));
        $info->addItem(new ListInfoItem('Unpaid Invoices', (new BadgeDanger())->setText(3)->setOutline()));

        $invoicesSummary = new Widget();
        $invoicesSummary->setTitle('Invoice Summary');
        $invoicesSummary->addElement($info);

        //Earning Summary
        $info = new ListInfo();
        $info->addItem(new ListInfoItem('Last Week', (new Badge())->setText("$6.41 USD")->setOutline()));
        $info->addItem(new ListInfoItem('Last Month', (new Badge())->setText("$42.79 USD")->setOutline()));
        $info->addItem(new ListInfoItem('Last Quarter', (new Badge())->setText("$88.50 USD")->setOutline()));
        $info->addItem(new ListInfoItem('Last Year', (new Badge())->setText("88.50 USD")->setOutline()));

        $earningSummary = new Widget();
        $earningSummary->setTitle('Earnings Summary');
        $earningSummary->addElement($info);

        //Grid
        $grid = new Grid();
        $grid->setRows([
            [[$invoicesSummary, 4]],
            [[$earningSummary, 4]]
        ]);

        $this->addElement($grid);
    }

    protected function serviceDetails()
    {
        $listInfo = new ListInfo();
        $listInfo->addItem(new ListInfoItem('Client', 'Test Tester'));
        $listInfo->addItem((new ListInfoItem('Product', (new Link())->setTitle('#4 - VPS #4')->setUrl('https://google.com')))->setElements([
            (new IconButtonDelete())->onClick((new ModalLoad(new UserDelete()))
                ->withParams([
                    'someParams' => 666
                ])),
            (new IconButtonEdit())->onClick(new ModalLoad(new UserDelete())),
        ]));
        $listInfo->addItem(new ListInfoItem('Total Income', '$63.97 USD'));
        $listInfo->addItem(new ListInfoItem('Credentials', '#1 Amazon AWS'));
        $listInfo->addItem(new ListInfoItem('Pricing Group', '#1 EC2-AWS'));
        $listInfo->addItem(new ListInfoItem('Billing Method', 'On the first day of month'));
        $listInfo->addItem(new ListInfoItem('Billing Type', 'Custom Details'));

        $widget = new Widget();
        $widget->setTitle('Service Details');
        $widget->addElement($listInfo);

        $grid = new Grid();
        $grid->setRows([
            [[$widget, 6]]
        ]);
        $this->addElement($grid);
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

    public function addAutoSaveForm()
    {
        $widget = new Widget();
        $widget->setTitle('Auto Save Form');
        $widget->addElement(new AutoSaveForm());

        $this->addElement($widget);
    }

    protected function addReloadForm()
    {
        $widget = new Widget();
        $widget->setTitle('Auto Save Form');
        $widget->addElement(new ReloadForm());

        $this->addElement($widget);
    }

    protected function addNavBar()
    {
        $navBar = new NavBar();
        $navBar->addItem((new NavBarItem())->setTitle($this->translate('Orders'))->setIcon('shopping-cart')->setUrl('https://google.com'));
        $navBar->addItem((new NavBarItem())->setTitle('Pricing')->setActive(true)->setIcon('money'));

        $this->addElement($navBar);
    }

    protected function addPageInfo()
    {
        $pageDescription = new PageDescription();
        $pageDescription->setImagePath(ModuleConstants::getFullPath('resources', 'assets', 'img', 'actions', 'backup-jobs.png'));
        $pageDescription->setTitle($this->translate('XXXX'));
        $pageDescription->setContent('Mailing lists allow you to use a single address to send message to multiple email addresses.
This feature is very useful when you need to send a newsletter or another update to a group of people.
Allowed actions on a mailing list:');

        $this->addElement($pageDescription);
    }

    protected function addInfo()
    {
        $info = new ListInfo();
        $info->addItem(new ListInfoItem('Status', new Status()));
        $info->addItem(new ListInfoItem('Hostname', 'myestdomain.com'));
        $info->addItem(new ListInfoItem('Memory', '2GB'));
        $info->addItem(new ListInfoItem('Backups', 'Enabled'));

        $widget = new Widget();
        $widget->addElement($info);
        $widget->setTitle('Server Information');

        $this->addElement($widget);
    }

    protected function addSimpleTable()
    {
        $table = new TableSimple();
        $table->setTextCentered();
//        $table->addColum(new Column('Name'));
//        $table->addColum(new Column('Value'));
        $table->addRecord(new Record([
            'Status',
            new Status(),
        ]));
        $table->addRecord(new Record([
            'Hostname',
            'myestdomain.com',
        ]));
        $table->addRecord(new Record([
            'Memory',
            '2GB',
        ]));
        $table->addRecord(new Record([
            'Backups',
            'Enabled',
        ]));

        $table->addRecord(new Record([
            'Backups',
            (new TextShowHide())->setText('MagicPassword'),
        ]));

        $widget = new Widget();
        $widget->addElement($table);
        $widget->setTitle('Server Information');

        $this->addElement($widget);
    }

    protected function addTiles()
    {
        $tiles = new TilesBar();
        $tiles->setTitle('Actions');
        $tiles->addTile((new TileButton())->setImagePath(ModuleConstants::getFullPath('resources', 'assets', 'img', 'actions', 'backup-jobs.png'))->setTitle('Image')->onClick(new ModalLoad(new UserDelete())));
        $tiles->addTile((new TileButton())->setImagePath(ModuleConstants::getFullPath('resources', 'assets', 'img', 'actions', 'backup-jobs.png'))->setTitle('Image 1'));
        $tiles->addTile((new TileButton())->setImagePath(ModuleConstants::getFullPath('resources', 'assets', 'img', 'actions', 'backup-jobs.png'))->setTitle('Kernel'));
        $tiles->addTile((new TileButton())->setImagePath(ModuleConstants::getFullPath('resources', 'assets', 'img', 'actions', 'backup-jobs.png'))->setTitle('xxx'));
        $tiles->addTile((new TileButton())->setImagePath(ModuleConstants::getFullPath('resources', 'assets', 'img', 'actions', 'backup-jobs.png'))->setTitle('xxx'));
        $tiles->addTile((new TileButton())->setImagePath(ModuleConstants::getFullPath('resources', 'assets', 'img', 'actions', 'backup-jobs.png'))->setTitle('xxx'));

        $this->addElement($tiles);
    }

    protected function addWidgets()
    {
        $form = new \ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Forms\Form();
        $this->addElement($form);
    }
}
