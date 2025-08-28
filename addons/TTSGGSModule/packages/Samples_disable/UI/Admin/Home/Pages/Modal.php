<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Home\Pages;

use ModulesGarden\TTSGGSModule\Components\CancelButton;
use ModulesGarden\TTSGGSModule\Components\FormBuilder;
use ModulesGarden\TTSGGSModule\Components\SubmitButton;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\AjaxTrait;


class Modal extends \ModulesGarden\TTSGGSModule\Components\Modal implements \ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface
{
    use AjaxTrait;

//    public function toArray()
//    {
//        echo '<pre>';
//        $r =  parent::toArray();
//        print_r($r);
//        exit;
//
//    }

    public function loadData(): void
    {
        /*$clients = \WHMCS\User\Client::select('id', 'firstname')->get()->toArray();

        $dataProv = new ArrayDataProvider();
        $dataProv->setDefaultSorting('id', 'desc')->setData($clients);
        $this->setDataProvider($dataProv);*/
    }

    public function loadHtml(): void
    {
        $form = new FormBuilder();
        $form->add('dupa', Text::class);
        $form->add('dupa2', Text::class);
        $form->add('dupa3', (new Text('dupa3')));
        $form->add('submit', SubmitButton::class);
        $form->add('cancel', CancelButton::class);
        $form->add('generator', (new RandomStringGeneratorButton('passwordGen'))
            ->onClickUpdateField('dupa')
        );
        $form->add('switcher', Switcher::class);

        $success = new SubmitButton();
        $success->setTitle('XXXX');
        $success->setCss('lu-btn lu-btn--success');
        $success->onClickSubmitForm($form->getId());

        $cancel = new Button();
        $cancel->setTitle('Cancel');
        $cancel->setCss('lu-btn ');

        $this->setTitle('Test!');
        $this->setType('success');
        $this->addElement($form);
        $this->addActionButton($success);
        $this->addActionButton($cancel);
    }
}
