<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\AjaxSearch\Forms;

use ModulesGarden\TSSGGSModule\Components\Form\AbstractForm;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\AjaxSearch\Fields\ClientsSearchField;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\AjaxSearch\Providers\AjaxSearchProvider;

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