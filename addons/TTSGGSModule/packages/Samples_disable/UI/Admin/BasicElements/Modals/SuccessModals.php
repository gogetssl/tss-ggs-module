<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\BasicElements\Modals;

use ModulesGarden\TTSGGSModule\Components\Alert\AlertSuccess;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TTSGGSModule\Components\Modal\ModalSuccess;
use ModulesGarden\TTSGGSModule\Core\Components\Action;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\BasicElements\Modals\Forms\Confirm;


class SuccessModals extends AbstractModals
{

    public function loadHtml(): void
    {
        parent::loadHtml();

        $this->setTitle($this->translate('Success Modals'));

        $this->simple();
        $this->withForm();
        $this->withAlert();
        $this->withAlertAndForm();
    }

    protected function simple()
    {
        $modal = new ModalSuccess();
        $modal->setTitle('Simple Modal');
        $modal->setContent('Wiesz co robisz?');

        $button = new ButtonSuccess();
        $button->setTitle('Simple Modal');
        $button->onClick(Action::modalOpen($modal));

        $this->addToToolbar($button);
    }

    protected function withForm()
    {
        $modal = new ModalSuccess();
        $modal->setTitle('Simple Modal With Form');
        $modal->setContent('Wiesz co robisz?');
        $modal->addElement(new Confirm());

        $button = new ButtonSuccess();
        $button->setTitle('Simple Modal With Form');
        $button->onClick(Action::modalOpen($modal));

        $this->toolbar->addElement($button);
    }

    protected function withAlert()
    {
        $alert = new AlertSuccess();
        $alert->setText('Wiesz co robisz?');

        $modal = new ModalSuccess();
        $modal->setTitle('Simple Modal With Alert');
        $modal->addElement($alert);

        $button = new ButtonSuccess();
        $button->setTitle('Simple Modal With Alert');
        $button->onClick(Action::modalOpen($modal));

        $this->toolbar->addElement($button);
    }

    protected function withAlertAndForm()
    {
        $alert = new AlertSuccess();
        $alert->setText('Wiesz co robisz?');

        $modal = new ModalSuccess();
        $modal->setTitle('Simple Modal With Alert');
        $modal->addElement($alert);
        $modal->addElement(new Confirm());

        $button = new ButtonSuccess();
        $button->setTitle('Simple Modal With Alert');
        $button->onClick(Action::modalOpen($modal));

        $this->toolbar->addElement($button);
    }

}