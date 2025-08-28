<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Forms\MarkdownEditor;

use ModulesGarden\TTSGGSModule\Components\Button\ButtonSubmitSuccess;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class MarkdownEditor extends \ModulesGarden\TTSGGSModule\Components\Form\Form implements AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $widget = new Widget();
        $widget->setTitle('Markdown Editor');
        $this->addElement($widget);

        $this->builder->setDefaultContainer($widget);

        $markdown = new \ModulesGarden\TTSGGSModule\Components\MarkdownEditor\MarkdownEditor();
        $markdown->setName('markdown');
        $this->builder->addElement($markdown);

        $this->builder->addElement(new ButtonSubmitSuccess());
    }
}