<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\AjaxSearch\Forms;

use ModulesGarden\TTSGGSModule\Components\Form\AbstractForm;
use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\AjaxSearch\Fields\ClientsSearchField;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\AjaxSearch\Providers\AjaxSearchProvider;

class AjaxSearchForm extends AbstractForm implements AdminAreaInterface
{
    protected string $provider = AjaxSearchProvider::class;
    protected string $providerAction = AjaxSearchProvider::ACTION_UPDATE;

    public function __construct()
    {
        parent::__construct();

        $widget = new Widget();
        $this->addElement($widget);

        $this->builder = BuilderCreator::twoColumnsInContainer($this, $widget);
        $this->builder->createSubmitButton();
    }

    public function loadHtml(): void
    {
        $this->builder->addField((new ClientsSearchField()));
    }
}