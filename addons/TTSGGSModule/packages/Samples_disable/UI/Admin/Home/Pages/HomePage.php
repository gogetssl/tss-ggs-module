<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Home\Pages;

use ModulesGarden\TTSGGSModule\App\UI\Admin\LoggerManager\Buttons\DeleteLoggerModalButton;
use ModulesGarden\TTSGGSModule\Components\DataTable\Column;
use ModulesGarden\TTSGGSModule\Components\DataTable\DataTable;
use ModulesGarden\TTSGGSModule\Core\Components\Enums\Color;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\DataProviders\QueryDataProvider;
use WHMCS\User\Client;

class HomePage extends DataTable implements AdminAreaInterface
{
    /*protected function replaceFieldFirstname($field, $data, $value)
    {
        return (new FormInputText())
                ->setValue($value)
                ->setValueField($field);
    }*/

    public function loadData(): void
    {

        $dataProv = new QueryDataProvider(Client::select('id', 'firstname'));
        $dataProv->setDefaultSorting('id', 'desc');
        $this->setDataProvider($dataProv);
    }

    public function loadHtml(): void
    {
        $this->addColumn((new Column('id'))
            ->setOrderable(DataProvider::SORT_DESC)
            ->setSearchable(true, Column::TYPE_INT))
            ->addColumn((new Column('firstname'))
                ->setSortable()
                ->setSearchable(true))
            ->addColumn((new Column('switcher')));

        $button = new IconButton();
        $button->setId('kupa!');
        //$button->setClasses('lu-btn lu-btn--danger');
        $button->setIcon('delete');
        $button->setText('Test!');
        $button->setTitle('XXXXX');
        $button->setType(Color::DANGER);
        $button->onClick(DeleteLoggerModalButton::class);

        $this->addActionButton($button);
        //$this->addActionButton((new Switcher()));
        // $this->addActionButton($button);

        $this->addMassActionButton($button);
        $this->addMassActionButton($button);
//        $this->addMassActionButton($button);
//        $this->addMassActionButton($button);
//        $this->addMassActionButton($button);
//        $this->addMassActionButton($button);
//
//        $clients = \WHMCS\User\Client::select('id', 'firstname')->get()->toArray();
//        $this->setRecords($clients);
    }
}
