<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms;


use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\CronStatus;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers\CronSettingsProvider;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

/**
 * One block per cron job - what it does, when it ran last and, right below, how often it runs - in one form with one
 * Save button.
 */
class CronConfigurationForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    /** job => [setting key of its frequency (null: runs with every start), choices] */
    const JOBS = [
        'ProductPricingUpdate'      => ['cron1', ['1d', '1w', '1mo', 'never']],
        'SSLCertificatesProcessing' => ['cron5', ['5m', '10m', '30m', '1h', '3h', 'never']],
        'SSLCertificates'           => ['cron2', ['1d', '12h', '1w', 'never']],
        'RenewalNotifyCertificate'  => ['cron3', ['1d', 'never']],
        'SSLAutoInstall'            => [null, []],
    ];

    const LABELS = [
        '5m' => 'every 5 minutes', '10m' => 'every 10 minutes', '30m' => 'every 30 minutes', '1h' => 'every hour', '3h' => 'every 3 hours',
        '12h' => 'every 12 hours', '1d' => 'every day', '1w' => 'every week', '1mo' => 'every month', 'never' => 'never',
    ];

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

        $status = CronStatus::rows();
        $script = dirname(__DIR__, 6) . '/cron/cron.php';

        foreach(self::JOBS as $job => [$key, $choices])
        {
            $block = new Widget();
            $block->setTitle($this->translate('name_' . $job));
            $this->addElement($block);

            $row  = isset($status[$job]) ? $status[$job] : ['last' => null, 'error' => ''];
            $last = $row['last'] === null
                ? '<span style="color:#e7000b">' . $this->translate('neverRun') . '</span>'
                : htmlspecialchars($row['last']) . ($row['error'] !== '' ? ' <span style="color:#a65f00">- ' . htmlspecialchars($row['error']) . '</span>' : '');

            $block->addElement((new Text())->setText(
                '<p>' . $this->translate('about_' . $job) . '</p>'
                . '<p class="text-muted"><b>' . $this->translate('lastRun') . '</b> ' . $last . '<br>'
                . '<b>' . $this->translate('command') . '</b> <code>php -q ' . $script . ' ' . $job . ' --force</code></p>'
                . ($key === null ? '<p><b>' . $this->translate('frequency') . '</b> ' . $this->translate('everyStart') . '</p>' : '')
            ));

            if($key === null)
            {
                continue;
            }

            $options = [];
            foreach($choices as $choice)
            {
                $options[$choice] = self::LABELS[$choice];
            }
            $field = new Dropdown();
            $field->required();
            $field->setDefaultValueAsFirstOption();
            $field->setName($key);
            $field->setOptions($options);
            $this->builder->addFieldInContainer($block, $field);
        }

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
