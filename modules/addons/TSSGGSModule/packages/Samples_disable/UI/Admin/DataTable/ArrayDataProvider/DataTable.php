<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\ArrayDataProvider;

use ModulesGarden\TSSGGSModule\Components\CopyPasswordInline\CopyPasswordInline;
use ModulesGarden\TSSGGSModule\Components\DataTable\Column;
use ModulesGarden\TSSGGSModule\Components\FormLabel\FormLabel;
use ModulesGarden\TSSGGSModule\Components\Label\LabelSuccess;
use ModulesGarden\TSSGGSModule\Components\ListSimple\ListSimple;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\DataProviders\ArrayDataProvider;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\Switchers\TaxExempt;
use WHMCS\User\Client;

class DataTable extends \ModulesGarden\TSSGGSModule\Components\DataTable\DataTable implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->setTitle('Client Array Data Provider');

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
            ->addColumn((new Column('taxexempt')));

//        $this->addMassActionButton(new UserEdit());
//        $this->addMassActionButton(new UserDelete());
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
        $clients = Client::select('id', 'firstname', 'lastname', 'taxexempt', 'companyname')->get()->toArray();

        $dataProv = new ArrayDataProvider($clients);
        $dataProv->setDefaultSorting('id', 'DESC');
        $this->setDataProvider($dataProv);
    }


    protected function parseDataSetRecords(): void
    {
        $this->dataSet->setFieldModifier('taxexempt', function($fieldName, $row, $fieldValue) {
            $label = new FormLabel();
            $label->addElement((new TaxExempt('XXX'))->setValue($fieldValue));

            return $label;
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
