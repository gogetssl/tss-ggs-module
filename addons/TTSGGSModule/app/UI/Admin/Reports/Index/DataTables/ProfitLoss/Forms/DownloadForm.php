<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\ProfitLoss\Forms;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\ProfitLoss\Providers\DownloadProvider;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Reports\Index\DataTables\ProfitLoss\Providers\FilterProvider;
use ModulesGarden\TTSGGSModule\Components\Button\Button;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TTSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Components\HiddenField\HiddenField;
use ModulesGarden\TTSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;


class DownloadForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->provider       = DownloadProvider::class;
        $this->providerAction = DownloadProvider::ACTION_CREATE;

    }

    public function loadHtml(): void
    {
        $this->setId('profitLossDownloadForm');
        $ajaxData = Request::get('ajaxData')?:[];

        foreach ($ajaxData as $key => $val)
        {
            $hiddenField = (new HiddenField())->setName($key)->setValue($val);
            $this->addElement($hiddenField);
        }

        $this->addElement((new ButtonSuccess())->setTitle($this->translate('downloadCsv'))->setIcon('file-document-outline')->onClick(new FormSubmit($this)));
    }
}