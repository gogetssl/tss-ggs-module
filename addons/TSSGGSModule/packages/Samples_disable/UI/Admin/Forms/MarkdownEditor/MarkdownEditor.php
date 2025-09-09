<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Forms\MarkdownEditor;

use ModulesGarden\TSSGGSModule\Components\Button\ButtonSubmitSuccess;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class MarkdownEditor extends \ModulesGarden\TSSGGSModule\Components\Form\Form implements AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $widget = new Widget();
        $widget->setTitle('Markdown Editor');
        $this->addElement($widget);

        $this->builder->setDefaultContainer($widget);

        $markdown = new \ModulesGarden\TSSGGSModule\Components\MarkdownEditor\MarkdownEditor();
        $markdown->setName('markdown');
        $this->builder->addElement($markdown);

        $this->builder->addElement(new ButtonSubmitSuccess());
    }
}