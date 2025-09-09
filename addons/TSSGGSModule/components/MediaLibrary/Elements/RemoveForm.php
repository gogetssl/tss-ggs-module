<?php

namespace ModulesGarden\TSSGGSModule\Components\MediaLibrary\Elements;

use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;

abstract class RemoveForm extends Form implements AjaxComponentInterface
{
    protected string $providerAction = CrudProvider::ACTION_DELETE;
}
