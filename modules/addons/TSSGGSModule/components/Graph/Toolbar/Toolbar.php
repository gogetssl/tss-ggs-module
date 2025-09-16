<?php

namespace ModulesGarden\TSSGGSModule\Components\Graph\Toolbar;

use ModulesGarden\TSSGGSModule\Core\Support\Facades\Translator;
use ModulesGarden\TSSGGSModule\Components\Form\AbstractForm;
use ModulesGarden\TSSGGSModule\Components\IconButton\IconButtonEdit;
use ModulesGarden\TSSGGSModule\Components\Modal\ModalEdit;
use ModulesGarden\TSSGGSModule\Core\Components\Action;

class Toolbar extends \ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar
{
    protected AbstractForm $form;

    public function loadHtml(): void
    {
        if (!isset($this->form))
        {
            return;
        }

        $this->form->onSubmit(Action::reloadParent());

        $modal = new ModalEdit();
        $modal->setTitle(Translator::getBasedOnNamespaces([get_class($this->form), ModalEdit::class], 'title'));
        $modal->addElement($this->form);

        $icon = new IconButtonEdit();
        $icon->setTitle(Translator::getBasedOnNamespaces([get_class($this->form) . "\EditButton", IconButtonEdit::class], 'title'));
        $icon->onClick(Action::modalOpen($modal));

        $this->addElement($icon);
    }

    public function setForm(AbstractForm $form):self
    {
        $this->form = $form;

        return $this;
    }
}
