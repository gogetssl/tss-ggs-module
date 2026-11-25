<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Import\Index\Forms;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Import\Index\Providers\TemplateDownloadProvider;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;


class TemplateDownloadForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->provider       = TemplateDownloadProvider::class;
        $this->providerAction = TemplateDownloadProvider::ACTION_CREATE;
    }

    public function loadHtml(): void
    {
        $this->builder = BuilderCreator::oneColumn($this);
        $this->setId('TemplateDownloadForm');
        $toolbar = new Toolbar();

        $toolbar->addElement(
            (new ButtonSuccess())
                ->setTitle($this->translate('download'))
                ->setIcon('file-document-outline')
                ->onClick(
                    new FormSubmit($this)
                )
        );

        $this->addElement($toolbar);
    }
}
