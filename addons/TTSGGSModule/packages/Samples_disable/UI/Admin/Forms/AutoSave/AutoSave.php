<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Forms\AutoSave;

use ModulesGarden\TTSGGSModule\Components\Button\ButtonSubmitSuccess;
use ModulesGarden\TTSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TTSGGSModule\Components\FormInputText\FormInputText;
use ModulesGarden\TTSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class AutoSave extends \ModulesGarden\TTSGGSModule\Components\Form\Form implements AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $widget = new Widget();
        $widget->setTitle('Auto Save Form (widget in form)');
        $this->addElement($widget);

        $this->builder->setDefaultContainer($widget);

        $this->addDropdown();
        $this->addInputText();
        $this->addSwitcher();
        $this->addButton();
    }

    protected function addDropdown()
    {
        $dropdown = new Dropdown();
        $dropdown->setName('drop1');
        $dropdown->setOptions([
            '1' => '1',
            '2' => '2',
            '3' => '3',
        ]);
        $dropdown->onChange(new FormSubmit($this));
        $dropdown->setDefaultValueAsFirstOption();

        $this->builder->addField($dropdown);
    }

    protected function addInputText()
    {
        $text = new FormInputText();
        $text->setName('dwa');
        $text->onChange(new FormSubmit($this));

        $this->builder->addField($text);
    }

    protected function addSwitcher()
    {
        $switcher = new Switcher();
        $switcher->onChange(new FormSubmit($this));

        $this->builder->addField($switcher);
    }

    protected function addButton()
    {
        $switcher = new ButtonSubmitSuccess();
        $switcher->onClick(new FormSubmit($this));

        $this->builder->addField($switcher);
    }
}