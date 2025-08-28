<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Import\Index\Forms;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Import\Index\Providers\ImportProvider;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Import\Index\Providers\TemplateDownloadProvider;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\ProfitLoss\Providers\FilterProvider;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TTSGGSModule\Components\DatePicker\DatePicker;
use ModulesGarden\TTSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TTSGGSModule\Components\HiddenField\HiddenField;
use ModulesGarden\TTSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TTSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\DownloadFileFromForm;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;


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