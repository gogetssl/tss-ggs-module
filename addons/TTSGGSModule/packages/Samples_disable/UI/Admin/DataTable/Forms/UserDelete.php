<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Forms;

use ModulesGarden\TTSGGSModule\Components\Form\AbstractForm;
use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TTSGGSModule\Components\HiddenField\HiddenField;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Providers\UserProvider;

class UserDelete extends AbstractForm implements AdminAreaInterface, AjaxComponentInterface
{
    protected string $provider = UserProvider::class;
    protected string $providerAction = 'delete';

    public function loadHtml(): void
    {
        BuilderCreator::simple($this)
            ->createField(HiddenField::class, 'id');
    }
}
