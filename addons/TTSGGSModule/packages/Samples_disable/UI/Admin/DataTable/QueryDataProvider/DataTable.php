<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\QueryDataProvider;

use ModulesGarden\TTSGGSModule\Components\CopyPasswordInline\CopyPasswordInline;
use ModulesGarden\TTSGGSModule\Components\DataTable\Column;
use ModulesGarden\TTSGGSModule\Components\FormLabel\FormLabel;
use ModulesGarden\TTSGGSModule\Components\Graph\Models\DataSet;
use ModulesGarden\TTSGGSModule\Components\IconButton\IconButton;
use ModulesGarden\TTSGGSModule\Components\Label\LabelSuccess;
use ModulesGarden\TTSGGSModule\Components\ListSimple\ListSimple;
use ModulesGarden\TTSGGSModule\Components\VisibilityWrapper\VisibilityWrapper;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\RedirectFromParam;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\DataProviders\QueryDataProvider;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Buttons\UserEdit;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Providers\ReorderTableProvider;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Switchers\TaxExempt;
use WHMCS\User\Client;

class DataTable extends \ModulesGarden\TTSGGSModule\Components\DataTable\DataTable implements AdminAreaInterface, AjaxComponentInterface
{
    //protected $recordsPerPage = 5;

    protected string $provider = ReorderTableProvider::class;

    public function loadHtml(): void
    {
        $this->setTitle('Clients');

        $this
            ->addColumn((new Column('id'))
                ->setSortable()
                ->setSearchable(true, Column::TYPE_INT))
            ->addColumn((new Column('firstname'))
                ->setSortable(true)
                ->setSearchable(true))
            ->addColumn((new Column('lastname'))
                ->setSortable(true)
                ->setSearchable(true))
            ->addColumn((new Column('taxexempt')))
            ->addColumn((new Column('graph')));

        $redirect = new IconButton();
        $redirect->setIcon('account');
        $redirect->onClick((new RedirectFromParam('url', [
            // 'redirectedId' => 'id'
        ])));


        $this->addActionButton($redirect);
        $this->addActionButton((new VisibilityWrapper(new UserEdit()))->disableWhen('id',"12")->hideWhen('id',"14"));
        $this->addActionButton(new \ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Buttons\UserDelete());
        $this->addMassActionButton((new \ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Buttons\UserDelete())->displayWithTitle("Delete"));

        $this->setDraggableRows();
//
//
//        $this->addActionButton(new  UserEdit());
//        $this->addActionButton(new  UserDelete());
//        $this->addToToolbar(new UserCreate());

        //Burger
//        $add = new DropdownMenuItem();
//        $add->setIcon('edit');
//
//        $dropdown = new DropdownMenu();
//        $dropdown->addItem($add);
//
//        $this->addToToolbar($dropdown);
    }

    public function loadData(): void
    {
        $clients = Client::select('tblclients.id', 'tblclients.firstname', 'lastname', 'taxexempt', 'companyname');

        $dataProv = new QueryDataProvider($clients);
        $dataProv->setColumns([
            new \ModulesGarden\TTSGGSModule\Core\DataProviders\Column('tblclients.id'),
            new \ModulesGarden\TTSGGSModule\Core\DataProviders\Column('tblclients.firstname'),
        ]);
        $dataProv->setDefaultSorting('tblclients.id', 'DESC');
        $this->setDataProvider($dataProv);
    }


    protected function parseDataSetRecords(): void
    {
        $this->dataSet->setFieldModifier('graph', function($fieldName, $row, $fieldValue) {
            $graph = rand(0,1) ? new \ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Graphs\Graphs\SparklineGraphLineInline() :
                new \ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Graphs\Graphs\SparklineGraphAreaInline();

            $dataSet = new DataSet();
            $dataSet->setTitle('Data Set')
                ->setData([rand(0, 10), rand(0, 10), rand(0, 10), rand(0, 10), rand(0, 10), rand(0, 10), rand(0, 10), rand(0, 10), rand(0, 10)]);

            $graph->addDataSet($dataSet);

            return $graph;
        });

        $this->dataSet->setFieldModifier('taxexempt', function($fieldName, $row, $fieldValue) {
            $label = new FormLabel();
            $label->addElement((new TaxExempt('XXX'))->setValue($fieldValue));

            return $label;
        });

        $this->dataSet->setFieldModifier('id', function($fieldName, &$row, $fieldValue) {
            $row['url'] = 'https://www.modulesgarden.com/products/whmcs/payment-gateway-charges#changelog';

            return $fieldValue;
        });


        $this->dataSet->setFieldModifier('lastname', function($fieldName, $row, $fieldValue) {
            return (new CopyPasswordInline())->setText($fieldValue);
        });

        $this->dataSet->setFieldModifier('firstname', function($fieldName, $row, $fieldValue) {
            $container = new ListSimple();
            $container->addItem((new LabelSuccess())->setText($fieldValue)->displayAsStatusLabel());
            $container->addItem((new LabelSuccess())->setText($row['companyname'])->displayAsStatusLabel());
            return $container;
        });


        $this->dataSet->modifyRecords();
    }
}
