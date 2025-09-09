<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\BasicElements\Modals;

use ModulesGarden\TSSGGSModule\Components\Alert\AlertDanger;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonDanger;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Components\Modal\ModalDanger;
use ModulesGarden\TSSGGSModule\Components\Modal\ModalEdit;
use ModulesGarden\TSSGGSModule\Components\Modal\ModalSuccess;
use ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Components\Action;

class BaseModals extends Widget
{
    protected $toolbar;

    public function loadHtml(): void
    {
        $this->createToolbar();
        $this->successModal();
        $this->deleteModal();
        $this->editModal();
    }

    protected function createToolbar()
    {
        $this->toolbar = new Toolbar();

        $this->addElement($this->toolbar);
    }

    protected function successModal()
    {
        $modal = new ModalSuccess();
        $modal->setTitle('Success Modal');
        $modal->setContent('Wiesz co robisz?');
        $modal->addElement(new Confirm());

        $button = new ButtonSuccess();
        $button->setTitle('Edit Modal');
        $button->onClick(Action::modalOpen($modal));


        $this->toolbar->addElement($button);
    }

    protected function deleteModal()
    {
        $modal = new ModalDanger();
        $modal->setTitle('Delete Modal');
        $modal->setContent('Content');

        $button = new ButtonDanger();
        $button->setTitle('Delete Modal');
        $button->onClick(Action::modalOpen($modal));

        $this->toolbar->addElement($button);
    }

    protected function editModal()
    {
        $modal = new ModalEdit();
        $modal->setTitle('Edit Modal');
        $modal->setContent('Content');

        $alert = new AlertDanger();
        $alert->setOutline();
        $alert->setText('Danger!');
        $modal->addElement($alert);

        $button = new ButtonPrimary();
        $button->setTitle('Edit Modal');
        $button->onClick(Action::modalOpen($modal));

        $this->toolbar->addElement($button);
    }
}