<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\StaticAjaxData;

use ModulesGarden\TTSGGSModule\Components\Form\AbstractForm;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class Form extends AbstractForm implements AjaxComponentInterface, AdminAreaInterface
{
    protected string $provider = FormProvider::class;
}