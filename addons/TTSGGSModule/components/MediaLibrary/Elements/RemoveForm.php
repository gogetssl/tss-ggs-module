<?php

namespace ModulesGarden\TTSGGSModule\Components\MediaLibrary\Elements;

use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;

abstract class RemoveForm extends Form implements AjaxComponentInterface
{
    protected string $providerAction = CrudProvider::ACTION_DELETE;
}
