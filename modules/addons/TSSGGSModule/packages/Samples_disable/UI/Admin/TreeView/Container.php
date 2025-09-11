<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TreeView;

use ModulesGarden\TSSGGSModule\Components\AppNavBar\Breadcrumb;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSubmitSuccess;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalLoad;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\PassAjaxData;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ClientAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Storage\Resources;
use ModulesGarden\TSSGGSModule\Core\UI\Interfaces\ClientArea;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TreeView\CronTasks\CronTasks;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TreeView\Forms\AutoSaveForm;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TreeView\Forms\ReloadForm;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TreeView\Modal\Clients;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TreeView\Modals\Base;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TreeView\Modals\SwitchersModal;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TreeView\Overview\Overview;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TreeView\ServiceDetails\ServiceDetails;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TreeView\ServiceInformation\ServiceInformation;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TreeView\SubPages\DataTable\DataTable;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TreeView\Tabs\Tabs;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TreeView\Tiles\Tiles;
use ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TreeView\TreeView\TreeView;

class Container extends \ModulesGarden\TSSGGSModule\Components\Container\Container implements AdminAreaInterface, ClientAreaInterface
{
    public function loadHtml(): void
    {
        $this->popup();
        $this->form();
        $this->buttons();


        $this->addElement(new TreeView());
    }

    protected function popup()
    {
        $button = new ButtonSuccess();
        $button->setTitle('Click Me!');
        $button->onClick(new ModalLoad( new \ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\TreeView\Modals\Clients()));

        $toolbar = new Toolbar();
        $toolbar->addElement($button);

        $widget = new Widget();
        $widget->setTitle('Popup field reload');
        $widget->addElement($toolbar);

        $this->addElement($widget);
    }

    protected function buttons()
    {
        $button = new ButtonSuccess();
        $button->setTitle('Reload datatable with Ajax Data');
        $button->onClick((new ReloadById('datatable'))->withParams([
            'dupa' => 'from-button'
        ]));

        $toolbar = new Toolbar();
        $toolbar->addElement($button);

        $widget = new Widget();
        $widget->setTitle('Control buttons');
        $widget->addElement($toolbar);

        $this->addElement($widget);
    }

    protected function form()
    {
        $input = new FormInputText();
        $input->setName('dupa');

        $form = new Form();
        $form->onSubmit((new PassAjaxData('datatable')));
        $form->onSubmit(new ReloadById('datatable'));
        $form->addElement($input);
        $form->addElement((new ButtonSubmitSuccess())->setTitle('Send Ajax Data And Reload')->onClick(new FormSubmit($form)));

        $widget = new Widget();
        $widget->addElement($form);

        $this->addElement($widget);
    }
}
