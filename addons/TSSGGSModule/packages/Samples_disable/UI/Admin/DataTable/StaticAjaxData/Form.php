<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\DataTable\StaticAjaxData;

use ModulesGarden\TSSGGSModule\Components\Form\AbstractForm;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class Form extends AbstractForm implements AjaxComponentInterface, AdminAreaInterface
{
    protected string $provider = FormProvider::class;
}