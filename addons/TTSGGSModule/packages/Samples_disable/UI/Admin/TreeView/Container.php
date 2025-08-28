<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView;

use ModulesGarden\TTSGGSModule\Components\AppNavBar\Breadcrumb;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonSubmitSuccess;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TTSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ModalLoad;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\PassAjaxData;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ClientAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Storage\Resources;
use ModulesGarden\TTSGGSModule\Core\UI\Interfaces\ClientArea;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView\CronTasks\CronTasks;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView\Forms\AutoSaveForm;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView\Forms\ReloadForm;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView\Modal\Clients;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView\Modals\Base;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView\Modals\SwitchersModal;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView\Overview\Overview;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView\ServiceDetails\ServiceDetails;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView\ServiceInformation\ServiceInformation;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView\SubPages\DataTable\DataTable;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView\Tabs\Tabs;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView\Tiles\Tiles;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView\TreeView\TreeView;

class Container extends \ModulesGarden\TTSGGSModule\Components\Container\Container implements AdminAreaInterface, ClientAreaInterface
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
        $button->onClick(new ModalLoad( new \ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView\Modals\Clients()));

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
