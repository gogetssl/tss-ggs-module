<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Forms;


use ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Components\RevertSwitcher;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Settings\Index\Providers\CronSettingsProvider;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TTSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TTSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TTSGGSModule\Components\Form\Form;
use ModulesGarden\TTSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;


class CronConfigurationForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    protected $configuration;

    public function __construct($configuration = false)
    {
        parent::__construct();

        $this->configuration  = $configuration;
        $this->provider       = CronSettingsProvider::class;
        $this->providerAction = CronSettingsProvider::ACTION_UPDATE;
    }

    public function loadHtml(): void
    {
        $this->builder = BuilderCreator::oneColumn($this);
        $this->setId('CronConfigurationForm');

        $field = new Dropdown();
        $field->required();
        $field->setDefaultValueAsFirstOption();
        $field->setName('cron1');
        $field->setOptions([
            //'5m' => 'every 5 minutes',
            //'10m' => 'every 10 minutes',
            //'30m' => 'every 30 minutes',
            //'1h' => 'every hour',
            //'3h' => 'every 3 hours',
            //'6h' => 'every 6 hours',
            //'12h' => 'every 12 hours',
            '1d' => 'every day',
            '1w' => 'every week',
            '1mo' => 'every month',
            'never' => 'never'
        ]);
        $this->builder->addField($field);

        $field = new Dropdown();
        $field->required();
        $field->setDefaultValueAsFirstOption();
        $field->setName('cron5');
        $field->setOptions([
            '5m' => 'every 5 minutes',
            '10m' => 'every 10 minutes',
            '30m' => 'every 30 minutes',
            '1h' => 'every hour',
            '3h' => 'every 3 hours',
            '6h' => 'every 6 hours',
            '12h' => 'every 12 hours',
            '1d' => 'every day',
            '1w' => 'every week',
            '1mo' => 'every month',
            'never' => 'never'
        ]);
        $this->builder->addField($field);

        $field = new Dropdown();
        $field->required();
        $field->setDefaultValueAsFirstOption();
        $field->setName('cron2');
        $field->setOptions([
            //'5m' => 'every 5 minutes',
            //'10m' => 'every 10 minutes',
            //'30m' => 'every 30 minutes',
            //'1h' => 'every hour',
            //'3h' => 'every 3 hours',
            //'6h' => 'every 6 hours',
            '12h' => 'every 12 hours',
            '1d' => 'every day',
            '1w' => 'every week',
            //'1mo' => 'every month',
            //'never' => 'never'
        ]);
        $this->builder->addField($field);

        $field = new Dropdown();
        $field->required();
        $field->setDefaultValueAsFirstOption();
        $field->setName('cron3');
        $field->setOptions([
            '5m' => 'every 5 minutes',
            '10m' => 'every 10 minutes',
            '30m' => 'every 30 minutes',
            '1h' => 'every hour',
            '3h' => 'every 3 hours',
            '6h' => 'every 6 hours',
            '12h' => 'every 12 hours',
            '1d' => 'every day',
            '1w' => 'every week',
            '1mo' => 'every month',
            'never' => 'never'
        ]);
        $this->builder->addField($field);


        $field = new Dropdown();
        $field->required();
        $field->setDefaultValueAsFirstOption();
        $field->setName('cron4');
        $field->setOptions([
            //'5m' => 'every 5 minutes',
            //'10m' => 'every 10 minutes',
            //'30m' => 'every 30 minutes',
            //'1h' => 'every hour',
            //'3h' => 'every 3 hours',
            //'6h' => 'every 6 hours',
            //'12h' => 'every 12 hours',
            '1d' => 'every day',
            '1w' => 'every week',
            '1mo' => 'every month',
            'never' => 'never'
        ]);
        $this->builder->addField($field);


        $toolbar = new Toolbar();
        $toolbar->addElement(
            (new ButtonSuccess())
                ->setId('buttonSave')
                ->setTitle($this->translate('save'))
                ->onClick(new FormSubmit($this))
        );

        $this->addElement($toolbar);
    }
}