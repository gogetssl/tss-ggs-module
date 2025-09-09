<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\Forms;

use ModulesGarden\TSSGGSModule\Components\Form\AbstractForm;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TSSGGSModule\Components\HiddenField\HiddenField;
use ModulesGarden\TSSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\Providers\UserProvider;

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
