<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\HintsBox\Forms;

use ModulesGarden\TTSGGSModule\Components\Form\AbstractForm;
use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TTSGGSModule\Components\Switcher\Switcher;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\HintsBox\Providers\SettingsProvider;

class SettingsForm extends AbstractForm implements AdminAreaInterface
{
    protected string $provider = SettingsProvider::class;
    protected string $providerAction = SettingsProvider::ACTION_UPDATE;

    public function __construct()
    {
        parent::__construct();

        $widget = new Widget();
        $this->addElement($widget);

        $this->builder = BuilderCreator::twoColumnsInContainer($this, $widget);
        $this->builder->createSubmitButton();
    }

    public function loadHtml(): void
    {
        $this->builder->createField(Switcher::class, 'hideGuide', true);
    }
}