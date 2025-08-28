<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Forms;

use ModulesGarden\TTSGGSModule\Components\Form\AbstractForm;
use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TTSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TTSGGSModule\Components\HiddenField\HiddenField;
use ModulesGarden\TTSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Providers\UserProvider;

class UserEdit extends AbstractForm implements AjaxComponentInterface, AdminAreaInterface
{
    protected string $provider = UserProvider::class;
    protected string $providerAction = 'update';

    public function loadHtml(): void
    {
        $builder = BuilderCreator::simple($this);

        $builder->createField(HiddenField::class, 'id');
        $builder->createField(FormInputText::class, 'firstname')
            ->setValidators(['required']);

        $builder->createField(FormInputText::class, 'lastname');
        $builder->createField(Switcher::class, 'taxexempt');
    }
}
