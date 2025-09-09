<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\ProfitLoss\Forms;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\ProfitLoss\Providers\DownloadProvider;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\ProfitLoss\Providers\FilterProvider;
use ModulesGarden\TSSGGSModule\Components\Button\Button;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\HiddenField\HiddenField;
use ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;


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